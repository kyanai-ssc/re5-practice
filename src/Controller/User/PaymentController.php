<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Error\ErrorLoggerTrait;
use App\Exception\ThreeDSecurePaymentException;
use App\Form\User\Payment\GmoThreeDSecureForm;
use App\Form\User\Payment\LinkPaymentForm;
use App\Form\User\Payment\SbPaymentThreeDSecureForm;
use App\Locale\Message;
use App\Model\Entity\PaymentMethod;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;
use Cake\Mailer\MailerAwareTrait;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Payment Controller
 */
class PaymentController extends UserAppController
{
    use ErrorLoggerTrait;
    use MailerAwareTrait;

    public const SESSION_KEY = [
        'reservationId' => 'payment.reservationId.%s',
        'authenticationId' => 'payment.authenticationId.%s', // SBペイメント3Dセキュア用
    ];

    protected const RESULT_OK = 'OK';
    protected const RESULT_NG = 'NG';

    /**
     * @var \App\Model\Entity\PaymentSetting
     */
    protected $paymentSetting;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        if ($this->getRequest()->getParam('action') !== 'result') {
            parent::initialize();
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        if ($this->getRequest()->getParam('action') !== 'result') {
            $this->FormProtection->setConfig('unlockedActions', [
                'finish',
                'cancel',
                'error',
                'errorSb3ds',
            ]);
            $this->isLoginRequire([
                'link',
                'finish',
                'cancel',
                'error',
                'errorSb3ds',
            ]);
        }

        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->fetchTable('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        $this->paymentSetting = $paymentSetting;

        return $response;
    }

    /**
     * Link method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function link($id = null)
    {
        $sessionKey = sprintf(static::SESSION_KEY['reservationId'], (string)$id);
        if ((string)$id === '' || (string)$id !== (string)$this->getRequest()->getSession()->read($sessionKey)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $linkPaymentForm = new LinkPaymentForm();
        $linkPaymentForm->setReservation($reservationsTable->get((int)$id, [
            'finder' => 'edit',
        ]));

        $this->set([
            'linkPaymentForm' => $linkPaymentForm,
        ]);
    }

    /**
     * Finish method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function finish($id = null)
    {
        $sessionKey = sprintf(static::SESSION_KEY['reservationId'], (string)$id);
        if ((string)$id === '' || (string)$id !== (string)$this->getRequest()->getSession()->read($sessionKey)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        try {
            /** @var \App\Model\Table\ReservationsTable $reservationsTable */
            $reservationsTable = $this->fetchTable('Reservations');
            /** @var \App\Model\Table\ReservationPaymentsTable $reservationPaymentsTable */
            $reservationPaymentsTable = $this->fetchTable('ReservationPayments');

            $reservation = $reservationsTable->get((int)$id, [
                'finder' => 'edit',
            ]);

            if ($this->paymentSetting->isPaymentServiceGmo()) {
                $this->getRequest()->allowMethod('post');

                $reservationPaymentsTable->getLockForThreeDSecure($reservation->get('id'));

                $gmoThreeDSecureForm = new GmoThreeDSecureForm();
                $gmoThreeDSecureForm->setReservation($reservation);

                if (!$gmoThreeDSecureForm->execute((array)$this->getRequest()->getData())) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }

                $paymentResult = $gmoThreeDSecureForm->getPaymentResult();
                if (!isset($paymentResult)) {
                    $reservationsTable->onThreeDSecureFail($reservation);

                    throw new BadRequestException(Message::ERROR_PAYMENT);
                }

                try {
                    $reservationsTable->onThreeDSecureSuccess($reservation, $paymentResult);
                } catch (Throwable $e) {
                    $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));

                    throw new ThreeDSecurePaymentException($e->__toString());
                }

                $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));
            } elseif ($this->paymentSetting->isPaymentServiceSb()) {
                $paymentMethod = $reservation->get('payment_method');
                $authenticationId = (string)$this->getRequest()->getSession()->read(
                    sprintf(static::SESSION_KEY['authenticationId'], (string)$id)
                );
                if (
                    $paymentMethod instanceof PaymentMethod
                    && $paymentMethod->isCard()
                    && $authenticationId !== ''
                ) {
                    // 決済方法がカードかつセッションに認証IDがあればクレジットカード3Dセキュア
                    $this->getRequest()->allowMethod('get');

                    $reservationPaymentsTable->getLockForThreeDSecure($reservation->get('id'));

                    $sbPaymentThreeDSecureForm = new SbPaymentThreeDSecureForm();
                    $sbPaymentThreeDSecureForm->setReservation($reservation);
                    $sbPaymentThreeDSecureForm->setPaymentSetting($this->paymentSetting);

                    // FormクラスでAPI送信
                    if (!$sbPaymentThreeDSecureForm->execute(['tds_authentication_id' => $authenticationId])) {
                        throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                    }

                    $paymentResult = $sbPaymentThreeDSecureForm->getPaymentResult();
                    if (!isset($paymentResult)) {
                        // 決済要求または確定要求でエラーの場合
                        try {
                            // 決済取消
                            $sbPaymentThreeDSecureForm->cancelPayment();
                        } catch (Throwable $e) {
                            $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));
                        }
                        $reservationsTable->onThreeDSecureFail($reservation);

                        throw new BadRequestException(Message::ERROR_PAYMENT);
                    }

                    try {
                        $reservationsTable->onThreeDSecureSuccess($reservation, $paymentResult);
                    } catch (Throwable $e) {
                        // 確定要求まで完了後にシステムエラーとなった場合
                        try {
                            // 決済取消
                            $sbPaymentThreeDSecureForm->cancelPayment();
                        } catch (Throwable $e2) {
                            $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));

                            /** @var \App\Mailer\AdminMailer $mailer */
                            $mailer = $this->getMailer('Admin');
                            $mailer->sendFailedToCancelPaymentOnSb($reservation);

                            throw new ThreeDSecurePaymentException($e2->__toString() . "\n" . $e->__toString());
                        }
                        $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));

                        throw $e;
                    }

                    $reservationPaymentsTable->releaseLockForThreeDSecure($reservation->get('id'));
                } else {
                    // リンク型決済
                    $this->getRequest()->allowMethod('post');

                    $linkPaymentForm = new LinkPaymentForm();
                    $linkPaymentForm->setReservation($reservation);

                    if (!$linkPaymentForm->execute((array)$this->getRequest()->getData())) {
                        throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                    }

                    $paymentResult = $linkPaymentForm->getPaymentResult();
                    if (!$paymentResult['result']) {
                        throw new BadRequestException(Message::ERROR_PAYMENT_LINK);
                    }
                }
            } else {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }

            $this->getRequest()->getSession()->delete($sessionKey);
            $this->getRequest()->getSession()->delete(sprintf(static::SESSION_KEY['authenticationId'], (string)$id));

            if ($reservation->isCanceled()) {
                throw new BadRequestException(Message::ERROR_THREE_D_SECURE_PAYMENT);
            }

            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'add-finish',
                '?' => [
                    'id' => [$id],
                ],
            ]);
        } catch (Throwable $e) {
            $this->writeRequestLog((int)$id);
            throw $e;
        }
    }

    /**
     * Cancel method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function cancel($id = null)
    {
        $sessionKey = sprintf(static::SESSION_KEY['reservationId'], (string)$id);
        if ((string)$id === '' || (string)$id !== (string)$this->getRequest()->getSession()->read($sessionKey)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        try {
            $this->getRequest()->allowMethod('post');

            /** @var \App\Model\Table\ReservationsTable $reservationsTable */
            $reservationsTable = $this->fetchTable('Reservations');

            $linkPaymentForm = new LinkPaymentForm();
            $linkPaymentForm->setReservation($reservationsTable->get((int)$id, [
                'finder' => 'edit',
            ]));

            if (!$linkPaymentForm->execute((array)$this->getRequest()->getData())) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }

            $reservationsTable->cancelReservation($linkPaymentForm->getReservation());

            $this->getRequest()->getSession()->delete($sessionKey);

            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'cancel-finish',
                'id' => $id,
            ]);
        } catch (Throwable $e) {
            $this->writeRequestLog((int)$id);
            throw $e;
        }
    }

    /**
     * Error method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function error($id = null)
    {
        $sessionKey = sprintf(static::SESSION_KEY['reservationId'], (string)$id);
        if ((string)$id === '' || (string)$id !== (string)$this->getRequest()->getSession()->read($sessionKey)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->getRequest()->allowMethod('post');

        $this->getRequest()->getSession()->delete($sessionKey);

        throw new BadRequestException(Message::ERROR_PAYMENT_LINK);
    }

    /**
     * SB Payment 3DS Error method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function errorSb3ds($id = null)
    {
        $sessionKey = sprintf(static::SESSION_KEY['reservationId'], (string)$id);
        $authenticationSessionKey = sprintf(static::SESSION_KEY['authenticationId'], (string)$id);
        if (
            (string)$id === ''
            || (string)$id !== (string)$this->getRequest()->getSession()->read($sessionKey)
            || (string)$this->getRequest()->getSession()->read($authenticationSessionKey) === ''
        ) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->getRequest()->getSession()->delete($sessionKey);
        $this->getRequest()->getSession()->delete($authenticationSessionKey);

        throw new BadRequestException(Message::ERROR_PAYMENT_LINK);
    }

    /**
     * Result method
     *
     * @param int|string|null $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function result($id = null)
    {
        try {
            $this->getRequest()->allowMethod('post');

            /** @var \App\Model\Table\ReservationsTable $reservationsTable */
            $reservationsTable = $this->fetchTable('Reservations');

            if (!$reservationsTable->validatePrimaryKey($id)) {
                $this->writeRequestLog();

                return $this->resultResponse('不正な値が送信されました。');
            }

            $linkPaymentForm = new LinkPaymentForm();
            $linkPaymentForm->setReservation($reservationsTable->get((int)$id, [
                'finder' => 'edit',
            ]));
            $linkPaymentForm->setForDisplayResult(false);

            if (!$linkPaymentForm->execute((array)$this->getRequest()->getData())) {
                $this->writeRequestLog((int)$id);

                return $this->resultResponse('不正な値が送信されました。');
            }

            $paymentResult = $linkPaymentForm->getPaymentResult();
            if (!$paymentResult['result']) {
                $this->writeRequestLog((int)$id);

                return $this->resultResponse();
            }

            $reservationsTable->onLinkPaymentSuccess($linkPaymentForm->getReservation(), $paymentResult);

            return $this->resultResponse();
        } catch (Throwable $e) {
            $this->getErrorLogger()->logException($e, $this->getRequest(), true);
            $this->writeRequestLog((int)$id);

            return $this->resultResponse('エラーが発生しました。');
        }
    }

    /**
     * 結果通知のレスポンス
     *
     * @param string|null $message メッセージ
     * @return \Cake\Http\Response
     */
    protected function resultResponse(?string $message = null)
    {
        if (!isset($message)) {
            $result = [static::RESULT_OK, ''];
        } else {
            $result = [static::RESULT_NG, $message];
        }

        $this->disableAutoRender();
        $this->setResponse(
            $this->getResponse()
                ->withCharset('SJIS')
                ->withType('text/csv')
                ->withStringBody(mb_convert_encoding(implode(',', $result), 'SJIS'))
        );

        return $this->getResponse();
    }

    /**
     * リクエストを記録
     *
     * @param int|null $dataId データID
     * @return void
     */
    protected function writeRequestLog(?int $dataId = null)
    {
        try {
            if (!$this->getRequest()->is('post')) {
                return;
            }

            $message = serialize((array)$this->getRequest()->getData());
            if (isset($dataId)) {
                $message = 'DataId: ' . $dataId . "\n" . $message;
            }
            Log::write(LogLevel::ERROR, $message, ['scope' => 'payment']);
        } catch (Throwable $e) {
            // DO NOTHING
        }
    }
}
