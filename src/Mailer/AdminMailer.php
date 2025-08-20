<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Event;
use App\Model\Entity\Reservation;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;

/**
 * AdminMailer class.
 */
class AdminMailer extends Mailer
{
    /**
     * @inheritDoc
     */
    protected function getBaseMailSetting($profile = null)
    {
        parent::getBaseMailSetting($profile);

        $this->setFrom(
            $this->getDefaultFromAddress(),
            Configure::readOrFail('Setting.mail.admin.fromName')
        );
        $this->setReturnPath($this->getAdminReturnPath());
    }

    /**
     * パスワードリセット通知
     *
     * @param string $to toアドレス
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @return void
     */
    public function adminPassReset(string $to, EntityInterface $entity)
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setTo($to)
            ->setSubject(Configure::readOrFail('Setting.mail.subject.AdminPasswordReset'))
            ->setViewVars(['token' => $entity->get('token')]);

        $this->viewBuilder()->setTemplate('admin-pass-reset');
    }

    /**
     * アップロード完了メールの送信
     *
     * @param string $modelName モデル名
     * @param array $mails メールアドレス
     * @param array $successInfo 完了情報
     * @return void
     */
    public function uploadFinishMail(string $modelName, array $mails, array $successInfo)
    {
        $noModify = false;
        if (ArrayUtility::inArray($modelName, Configure::readOrFail('Setting.csv.import.noModify'))) {
            $noModify = true;
        }

        $this->getBaseMailSetting('adminSend');
        $this
            ->setTo($mails)
            ->setSubject(Configure::readOrFail('Setting.mail.subject.' . $modelName))
            ->setViewVars(['successInfo' => $successInfo, 'noModify' => $noModify]);

        $this->viewBuilder()->setTemplate('admin-import-finish');
    }

    /**
     * アップロードエラーメールの送信
     *
     * @param string $modelName モデル名
     * @param array $mails メールアドレス
     * @param array $successInfo 完了情報
     * @return void
     */
    public function uploadErrorMail(string $modelName, array $mails, array $successInfo)
    {
        $noModify = false;
        if (ArrayUtility::inArray($modelName, Configure::readOrFail('Setting.csv.import.noModify'))) {
            $noModify = true;
        }

        $this->getBaseMailSetting('adminSend');
        $this
            ->setTo($mails)
            ->setSubject(Configure::readOrFail('Setting.mail.errorSubject.' . $modelName))
            ->setViewVars(['successInfo' => $successInfo, 'noModify' => $noModify]);

        $this->viewBuilder()->setTemplate('admin-import-error');
    }

    /**
     * お問い合わせ通知メールの送信
     *
     * @param \Cake\Datasource\EntityInterface $entity inquiry
     * @param string $adminMail adminMails
     * @return void
     */
    public function inquiry(EntityInterface $entity, string $adminMail)
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setReplyTo($entity->get('mail'))
            ->setTo($adminMail)
            ->setSubject(Configure::readOrFail('Setting.mail.subject.Inquiry'))
            ->setViewVars(['inquiry' => $entity]);

        $this->viewBuilder()->setTemplate('inquiry');
    }

    /**
     * スマートロック連携エラーメールの送信
     *
     * @param array $mails メールアドレス
     * @param array $info メール送信情報
     * @param string $subject 件名
     * @param string $template テンプレート
     * @return void
     */
    public function smartLockErrorMail(array $mails, array $info, string $subject, string $template): void
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setTo($mails)
            ->setSubject($subject)
            ->setViewVars([
                'info' => $info,
                'now' => $this->commonData()->getNowDateTime(),
            ]);

        $this->viewBuilder()->setTemplate($template);
    }

    /**
     * キャンセル済み予約の決済アラート
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @return void
     */
    public function sendPayCanceledReservation(Reservation $reservation): void
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.payCanceledReservation'))
            ->setViewVars([
                'reservation' => $reservation,
            ]);

        if ($paymentSetting->isPaymentServiceGmo()) {
            $this->viewBuilder()->setTemplate('pay_canceled_reservation_gmo_3ds');
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            $this->viewBuilder()->setTemplate('pay_canceled_reservation');
        }

        $labelId = null;
        $event = $reservation->getEventEntity();
        if ($event instanceof Event) {
            $labelId = $event->get('label_id');
        }

        foreach ($this->getReservationAdminMails($labelId) as $adminMail) {
            $email = clone $this;
            $email->setTo($adminMail->get('mail'));
            $email->setMessageId(true);
            $this->sendSafe($email, true);
        }
    }

    /**
     * 未決済予約のキャンセル通知
     *
     * @param array $reservationIds 予約ID
     * @return void
     */
    public function sendCancelNoPaymentReservation(array $reservationIds): void
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.cancelNoPaymentReservation'));

        if ($paymentSetting->isPaymentServiceGmo()) {
            $this->viewBuilder()->setTemplate('cancel_no_payment_reservation_gmo_3ds');
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            $this->viewBuilder()->setTemplate('cancel_no_payment_reservation');
        }

        foreach ($reservationIds as $labelId => $ids) {
            if ((string)$labelId !== '') {
                $labelId = (int)$labelId;
            } else {
                $labelId = null;
            }
            foreach ($this->getReservationAdminMails($labelId) as $adminMail) {
                $email = clone $this;
                $email->setTo($adminMail->get('mail'));
                $email->setMessageId(true);
                $email->setViewVars([
                    'reservationIds' => $ids,
                ]);
                $this->sendSafe($email, true);
            }
        }
    }

    /**
     * 未決済予約のキャンセル失敗通知
     *
     * @param array $reservationIds 予約ID
     * @param array $errorCodes エラーコード
     * @return void
     */
    public function sendFailedToCancelNoPaymentReservation(array $reservationIds, array $errorCodes = []): void
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getDataOrFail();

        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.failedToCancelNoPaymentReservation'));

        if ($paymentSetting->isPaymentServiceGmo()) {
            $this->viewBuilder()->setTemplate('failed_to_cancel_no_payment_reservation_gmo_3ds');
        } elseif ($paymentSetting->isPaymentServiceSb()) {
            $this->viewBuilder()->setTemplate('failed_to_cancel_no_payment_reservation');
        }

        foreach ($reservationIds as $labelId => $ids) {
            if ((string)$labelId !== '') {
                $labelId = (int)$labelId;
            } else {
                $labelId = null;
            }
            foreach ($this->getReservationAdminMails($labelId) as $adminMail) {
                $email = clone $this;
                $email->setTo($adminMail->get('mail'));
                $email->setMessageId(true);
                $email->setViewVars([
                    'reservationIds' => $ids,
                    'errorCodes' => $errorCodes,
                ]);
                $this->sendSafe($email, true);
            }
        }
    }

    /**
     * 未決済予約の通知
     *
     * @param array $reservationIds 予約ID
     * @return void
     */
    public function sendNoticeNoPaymentReservation(array $reservationIds): void
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.noticeNoPaymentReservation'));
        $this->viewBuilder()->setTemplate('notice_no_payment_reservation');

        foreach ($reservationIds as $labelId => $ids) {
            if ((string)$labelId !== '') {
                $labelId = (int)$labelId;
            } else {
                $labelId = null;
            }
            foreach ($this->getReservationAdminMails($labelId) as $adminMail) {
                $email = clone $this;
                $email->setTo($adminMail->get('mail'));
                $email->setMessageId(true);
                $email->setViewVars([
                    'reservationIds' => $ids,
                ]);
                $this->sendSafe($email, true);
            }
        }
    }

    /**
     * 返金連携エラーメールの送信
     *
     * @param \App\Model\Entity\Reservation $reservation 予約
     * @param string $errorCode エラーコード
     * @return void
     */
    public function sendFailedRefundPaymentReservation(Reservation $reservation, string $errorCode): void
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.failedRefundPaymentReservation'))
            ->setViewVars([
                'reservationId' => $reservation->get('id'),
                'errorCode' => $errorCode,
            ]);
        $this->viewBuilder()->setTemplate('failed_refund_payment_reservation');

        $categoryId = $reservation->getEventEntity() !== null ?
            (int)$reservation->getEventEntity()->get('label_id') : null;

        foreach ($this->getReservationAdminMails($categoryId) as $adminMail) {
            $email = clone $this;
            $email->setTo($adminMail->get('mail'));
            $email->setMessageId(true);
            $this->sendSafe($email, true);
        }
    }

    /**
     * 決済取消連携失敗メールの送信（SB）
     *
     * @param \App\Model\Entity\Reservation $reservation 予約エンティティ
     * @return void
     */
    public function sendFailedToCancelPaymentOnSb(Reservation $reservation): void
    {
        $this->getBaseMailSetting('adminSend');
        $this
            ->setFrom($this->getAdminReturnPath())
            ->setSubject(Configure::readOrFail('Setting.mail.subject.failedToCancelPaymentOnSb'))
            ->setViewVars([
                'reservationId' => $reservation->get('id'),
            ]);
        $this->viewBuilder()->setTemplate('failed_to_cancel_payment_on_sb');

        $labelId = null;
        $event = $reservation->getEventEntity();
        if ($event instanceof Event) {
            $labelId = $event->get('label_id');
        }

        /** @var \App\Model\Entity\AdminMail $adminMail */
        foreach ($this->getReservationAdminMails($labelId) as $adminMail) {
            $email = clone $this;
            $email
                ->setTo($adminMail->get('mail'))
                ->setMessageId(true);
            $this->sendSafe($email, true);
        }
    }

    /**
     * 予約関連のメールを受け取る管理者アドレスを取得
     *
     * @param int|null $labelId カテゴリID
     * @return array
     */
    protected function getReservationAdminMails(?int $labelId): array
    {
        $adminMailsTable = $this->getTableLocator()->get('AdminMails');

        return $adminMailsTable->find('autoReplyMail', [
            'inputs' => [
                'label_id' => $labelId,
            ],
        ])->toArray();
    }
}
