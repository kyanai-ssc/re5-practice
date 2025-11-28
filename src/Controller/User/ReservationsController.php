<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\OptinTrait;
use App\Controller\Traits\ReservationsTrait;
use App\Controller\UserAppController;
use App\Form\User\Reservations\CalendarForm;
use App\Form\User\Reservations\CancelForm;
use App\Form\User\Reservations\ContinuousForm;
use App\Form\User\Reservations\HistoryForm;
use App\Form\User\Reservations\OptinForm;
use App\Form\User\Reservations\ReservationForm;
use App\Locale\Message;
use App\Model\Entity\OptinToken;
use App\Model\Entity\RecaptchaSetting;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Routing\Router;

/**
 * Reservations Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class ReservationsController extends UserAppController
{
    use OptinTrait;
    use ReservationsTrait;

    public const TOKEN_VALIDATION_ADD = 'reservations_add';
    public const TOKEN_VALIDATION_EDIT = 'reservations_edit';

    public const CALENDAR_FRAME_CURRENT_WINDOW = 1;
    public const CALENDAR_FRAME_NEW_WINDOW = 2;

    public const CALENDAR_FRAME = [
        self::CALENDAR_FRAME_CURRENT_WINDOW,
        self::CALENDAR_FRAME_NEW_WINDOW,
    ];

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadFileUploadComponent();
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'calendar',
            'mailAdd',
            'mailAddFinish',
            'token',
            'add',
            'addConf',
            'addFinish',
        ]);

        $this->FormProtection->setConfig('unlockedActions', [
            'cancel',
        ]);

        return $response;
    }

    /**
     * History method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function history()
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('reservations.history.list');
        }

        // 入力値取得
        $searchForm = new HistoryForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('reservations.history.list')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('reservations.history.list')
            );
        }

        $identity = $this->Authentication->getIdentity();
        $userId = null;
        if (isset($identity)) {
            /** @var \Authentication\Identity $identity */
            $userId = $identity->offsetGet('id');
        }
        $searchData = $searchForm->setFixedValue($searchData, $userId);
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $reservations = $this->Pagination->paginate($this->fetchTable('Reservations'), [
            'finder' => [
                'searchHistory' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('reservations.history.list', $searchData);

        // ビュー変数
        $this->set([
            'reservations' => $reservations,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Calendar method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendar()
    {
        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        $calendarFrame = null;
        if (ArrayUtility::inArray($this->getRequest()->getQuery('frame'), static::CALENDAR_FRAME)) {
            $sessionKey = 'reservations.calendar.frame';
            $calendarFrame = $this->getRequest()->getQuery('frame');
        } elseif (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarForm = new CalendarForm();
        $calendarForm->setLabelId($this->commonData()->getUserLabelId());

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm);

        $this->enableLoginRedirectBack();
        $this->set([
            'calendarFrame' => $calendarFrame,
        ]);
        if (isset($calendarFrame)) {
            $this->Frame->allow();
        }
    }

    /**
     * Mail method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailAdd()
    {
        $reservationForm = new ReservationForm();
        $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
        if (!$reservationForm->validateReservationParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $result = $this->mailAddAction(OptinToken::TYPE_RESERVATION, $reservationForm->getReservationParameter());
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'mail-add-finish',
            ]);
        }

        $this->set([
            'reservationForm' => $reservationForm,
        ]);
    }

    /**
     * Mail finish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailAddFinish()
    {
        $this->mailAddFinishAction();
    }

    /**
     * token method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function token()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $reservationForm = new ReservationForm();
        $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
        if (!$reservationForm->validateReservationParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $optinForm = new OptinForm();
        $this->tokenAction($optinForm);
        if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            $this->getRequest()->getSession()->write('reservationOptin', $optinForm->getOptinData());
        }

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'Reservations',
            'action' => 'add',
            '?' => $reservationForm->getReservationParameter(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $optinData = [];
        if (!$this->commonData()->existsUserLoginData()) {
            if (
                !$siteSettingsTable->getData()->isUseFlgOn('reservation_add_user_flg')
                && !$siteSettingsTable->getData()->isUseFlgOn('reservation_add_not_user_flg')
            ) {
                $reservationForm = new ReservationForm();
                $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
                if (!$reservationForm->validateReservationParameter()) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Auth',
                    'action' => 'login',
                    '?' => [
                        'redirect' => Router::url([
                            'prefix' => 'User',
                            'controller' => 'Reservations',
                            'action' => 'add',
                            '?' => $reservationForm->getReservationParameter(),
                        ]),
                    ],
                ]);
            }

            if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
                $optinForm = new OptinForm();
                if (!$this->getRequest()->getSession()->check('reservationOptin')) {
                    if (!$this->getRequest()->is('post')) {
                        $reservationForm = new ReservationForm();
                        $reservationForm->setReservationParameter((array)$this->getRequest()->getQuery());
                        if (!$reservationForm->validateReservationParameter()) {
                            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                        }

                        return $this->redirect([
                            'prefix' => 'User',
                            'controller' => 'Reservations',
                            'action' => 'mail-add',
                            '?' => $reservationForm->getReservationParameter(),
                        ]);
                    }
                    throw new BadRequestException(Message::ERROR_INVALID_URL);
                }
                if (
                    !$optinForm->execute(
                        (array)$this->getRequest()->getSession()->read('reservationOptin.optinToken')
                    )
                ) {
                    $this->getRequest()->getSession()->delete('reservationOptin');
                    throw new BadRequestException(Message::ERROR_INVALID_URL);
                }
                $optinData = (array)$this->getRequest()->getSession()->read('reservationOptin.optinData');
            }
        }

        $reservationForm = new ReservationForm();
        $reservationForm->setOptinData($optinData);

        $result = $this->addAction($reservationForm, static::TOKEN_VALIDATION_ADD);
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'add-conf',
                '?' => [
                    'key' => $reservationForm->getContinuousParameter('key'),
                ],
            ]);
        }

        if (!$this->getRequest()->is('post') && $this->getRequest()->getQuery('input') !== 'back') {
            $error = $reservationForm->checkUserError();
            if (isset($error)) {
                throw new BadRequestException($error);
            }
        }

        $this->enableLoginRedirectBack();
    }

    /**
     * Add conf method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addConf()
    {
        // reCATPTCHA
        if ($this->getRequest()->is('post')) {
            $this->Recaptcha->verify(RecaptchaSetting::ACTION_RESERVE);
        }

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $optinData = [];
        $optinToken = null;
        if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg') && !$this->commonData()->existsUserLoginData()) {
            $optinForm = new OptinForm();
            if (!$this->getRequest()->getSession()->check('reservationOptin')) {
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            if (!$optinForm->execute((array)$this->getRequest()->getSession()->read('reservationOptin.optinToken'))) {
                $this->getRequest()->getSession()->delete('reservationOptin');
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $optinData = (array)$this->getRequest()->getSession()->read('reservationOptin.optinData');
            $optinToken = $optinForm->getOptinToken();
        }

        $continuousForm = new ContinuousForm();
        $continuousForm->setOptinData($optinData);

        $kycValues = $continuousForm->getKycValues($this->getRequest()->getData());

        $result = $this->addConfAction(
            $continuousForm,
            static::TOKEN_VALIDATION_ADD,
            Configure::readOrFail('Master.common.flg.on'),
            $optinToken,
            $this->getRequest()->getData('payment_method_id'),
            null,
            $kycValues
        );
        if ($result) {
            $this->getRequest()->getSession()->delete('reservationOptin');

            if ($continuousForm->isLinkPayment()) {
                $reservationId = $continuousForm->getFirstReservationId();
                $this->getRequest()->getSession()->write(
                    sprintf(PaymentController::SESSION_KEY['reservationId'], $reservationId),
                    $reservationId
                );

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Payment',
                    'action' => 'link',
                    'id' => $reservationId,
                ]);
            } elseif ($continuousForm->isThreeDSecure()) {
                $reservationId = $continuousForm->getFirstReservationId();
                $this->getRequest()->getSession()->write(
                    sprintf(PaymentController::SESSION_KEY['reservationId'], $reservationId),
                    $reservationId
                );
                // SBペイメント3Dセキュア用に認証IDをセッションに保存
                $this->getRequest()->getSession()->write(
                    sprintf(PaymentController::SESSION_KEY['authenticationId'], $reservationId),
                    $continuousForm->getThreeDSecureAuthenticationId()
                );

                return $this->redirect($continuousForm->getThreeDSecureUrl());
            } else {
                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'Reservations',
                    'action' => 'add-finish',
                    '?' => [
                        'id' => $continuousForm->getReservationIds(),
                    ],
                ]);
            }
        }
    }

    /**
     * Add finish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addFinish()
    {
        $this->addFinishAction();

        // 予約中のデータが存在する場合
        $remainReservations = false;
        if ($this->getRequest()->getSession()->check('reservations.add.continuousData')) {
            $remainReservations = true;
            $this->Flash->set((string)__(Message::ERROR_RESERVATION_CONTINUOUS_PARTIAL), [
                'key' => 'reservationsError',
                'element' => 'error',
            ]);
        }

        $this->set([
            'remainReservations' => $remainReservations,
        ]);
    }

    /**
     * View method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $reservationForm = new ReservationForm();

        $this->viewAction($id, $reservationForm);
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        $reservationForm = new ReservationForm();

        $result = $this->editAction($id, $reservationForm, static::TOKEN_VALIDATION_EDIT);
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'edit-conf',
                'id' => $reservationForm->getReservationEntity()->get('id'),
            ]);
        }
    }

    /**
     * Edit conf method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function editConf($id = null)
    {
        $reservationForm = new ReservationForm();

        $result = $this->editConfAction(
            $id,
            $reservationForm,
            static::TOKEN_VALIDATION_EDIT,
            Configure::readOrFail('Master.common.flg.on')
        );
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'edit-finish',
                'id' => $reservationForm->getReservationEntity()->get('id'),
            ]);
        }
    }

    /**
     * Edit finish method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function editFinish($id = null)
    {
        $this->editFinishAction($id);
    }

    /**
     * Cancel method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function cancel($id = null)
    {
        $cancelForm = new CancelForm();

        $this->cancelAction($id, $cancelForm, Configure::readOrFail('Master.common.flg.on'));

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'Reservations',
            'action' => 'cancel-finish',
            'id' => $id,
        ]);
    }

    /**
     * Cancel finish method
     *
     * @param int $id 予約ID
     * @return \Cake\Http\Response|null|void
     */
    public function cancelFinish($id = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        if (!$reservationsTable->validatePrimaryKey($id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'reservationId' => $id,
        ]);
    }
}
