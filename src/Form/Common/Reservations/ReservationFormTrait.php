<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\User\Reservations\ReservationForm;
use App\Locale\Message;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\Reservation;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\Table\ReservationsTable;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use DateInterval;
use DatePeriod;

/**
 * ReservationForm trait.
 */
trait ReservationFormTrait
{
    use ContinuousTrait;

    /**
     * @var array
     */
    protected $reservationParameter = null;

    /**
     * @var \App\Model\Entity\Reservation|null
     */
    protected $reservationEntity = null;

    /**
     * @var \App\Model\Entity\Event|null
     */
    protected $eventEntity = null;

    /**
     * @var \App\Model\Entity\User|null
     */
    protected $userEntity = null;

    /**
     * @var array|null
     */
    protected $reservationFormGroups = null;

    /**
     * @var bool
     */
    protected $excludeUserData = false;

    /**
     * @var bool
     */
    protected $isChangeForm = false;

    /**
     * @var \App\Model\Entity\Reservation|null
     */
    protected $repeatReservationDataEntity = null;

    /**
     * 予約のパラメータを取得
     *
     * @param string|null $key キー
     * @return mixed パラメータ
     */
    public function getReservationParameter(?string $key = null)
    {
        if (!isset($key)) {
            return $this->reservationParameter;
        }

        return Hash::get($this->reservationParameter, $key);
    }

    /**
     * 予約のパラメータを設定
     *
     * @param array $reservationParameter パラメータ
     * @return void
     */
    public function setReservationParameter(array $reservationParameter)
    {
        $this->reservationParameter = $reservationParameter;
    }

    /**
     * 予約のパラメータを検証
     *
     * @return bool 検証結果
     */
    public function validateReservationParameter()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $parametersData = $reservationsTable->getReservationParametersData(
            $this->getReservationParameter(),
            $this->getContinuousParameter(),
            $this->reservationEntity,
            $this->isAdmin()
        );
        if (isset($parametersData['errors'])) {
            $this->setErrors($parametersData['errors']);

            return false;
        }

        if (!$this->isAdmin() && !$this->reservationEntity) {
            $labelIds = $labelsTable->getLabelIdByUserAuthority();
            $eventLabelId = null;
            $event = null;
            $eventId = $parametersData['parameters']['event_id'];
            /** @var \App\Model\Entity\Event|null $event */
            $event = $eventsTable->find()->select(['label_id'])->where(['id' => (int)$eventId])->first();

            if ($event) {
                $eventLabelId = (int)$event->get('label_id');
            }
            if (!in_array($eventLabelId, $labelIds)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
        }

        $this->setReservationParameter($parametersData['parameters']);
        if (isset($parametersData['event'])) {
            $this->eventEntity = $parametersData['event'];
        }
        if (isset($parametersData['user'])) {
            $this->userEntity = $parametersData['user'];
        }

        return true;
    }

    /**
     * 予約枠のエンティティーを取得
     *
     * @return \App\Model\Entity\Event|null エンティティー
     */
    public function getEventEntity()
    {
        if (!isset($this->eventEntity)) {
            return null;
        }

        return $this->eventEntity;
    }

    /**
     * 予約のエンティティーを初期化
     *
     * @param array|string|null $data データ
     * @return void
     */
    public function initializeReservationEntity($data = null)
    {
        $this->createUserEntity((array)$data, [
            'validate' => false,
        ]);
        $this->createReservationEntity((array)$data, [
            'validate' => false,
        ]);
    }

    /**
     * 予約のエンティティーを取得
     *
     * @return \App\Model\Entity\Reservation エンティティー
     */
    public function getReservationEntity()
    {
        if (!isset($this->reservationEntity)) {
            throw new CakeException();
        }

        return $this->reservationEntity;
    }

    /**
     * 予約のエンティティーを設定
     *
     * @param \App\Model\Entity\Reservation $reservationEntity エンティティー
     * @return void
     */
    public function setReservationEntity(Reservation $reservationEntity)
    {
        $this->reservationEntity = $reservationEntity;
    }

    /**
     * 予約のフォームグループを取得
     *
     * @param int|null $formType フォームタイプ
     * @return array フォームグループ
     */
    public function getReservationFormGroups(?int $formType = null)
    {
        if (!isset($this->reservationFormGroups)) {
            if (!empty($this->getErrors()) || !isset($this->eventEntity)) {
                return [];
            }

            /** @var \App\Model\Table\ReservationsTable $reservationsTable */
            $reservationsTable = $this->getTableLocator()->get('Reservations');

            $userId = null;
            if (isset($this->userEntity)) {
                $userId = $this->userEntity->get('id');
            }
            $reservationId = null;
            if (isset($this->reservationEntity)) {
                $reservationId = $this->reservationEntity->get('id');
            }
            $this->reservationFormGroups = $reservationsTable->getReservationFormGroups(
                $this->eventEntity,
                (int)$this->getReservationParameter('user_authority_id'),
                [
                    'userId' => $userId,
                    'reservationId' => $reservationId,
                    'reservation' => $this->reservationEntity,
                    'reservationType' => $this->getReservationParameter('reservation_type'),
                    'usageTimestampFrom' => $this->getReservationParameter('usage_timestamp_from'),
                    'excludeUserData' => $this->excludeUserData,
                    'isConfirm' => $this->isConfirm(),
                ]
            );
        }

        if (!isset($formType)) {
            return $this->reservationFormGroups;
        }

        return Hash::get($this->reservationFormGroups, (string)$formType);
    }

    /**
     * アプリケーションルールのエラーを取得
     *
     * @param bool $includeStockError 予約数エラー
     * @return string|null
     */
    public function getRulesError($includeStockError = false)
    {
        if (isset($this->userEntity)) {
            $userErrors = $this->userEntity->getErrors();
            if (isset($userErrors['user_error'])) {
                return reset($userErrors['user_error']);
            }
        }
        if (isset($this->reservationEntity)) {
            $reservationErrors = $this->reservationEntity->getErrors();
            if (isset($reservationErrors['event_error'])) {
                return reset($reservationErrors['event_error']);
            }
            if ($includeStockError) {
                foreach ((array)$this->reservationEntity->get('reservation_options') as $reservationOption) {
                    if (isset($reservationErrors['option_errors_' . $reservationOption->get('option_id')])) {
                        return reset($reservationErrors['option_errors_' . $reservationOption->get('option_id')]);
                    }
                }
            }
        }

        return null;
    }

    /**
     * 個別の料金内訳表示を判定
     *
     * @return bool
     */
    public function requiredChargeBreakdownEach()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        return $siteSettingsTable->getData()->isUseFlgOn('charge_breakdown_flg') && $this->hasCharge();
    }

    /**
     * 料金内訳を取得
     *
     * @return array
     */
    public function getChargeBreakdown()
    {
        return Hash::get($this->getReservationEntity()->getChargeBreakdown(), 'breakdown', []);
    }

    /**
     * 料金の有無を判定
     *
     * @return bool
     */
    public function hasCharge()
    {
        return $this->getReservationEntity()->calculateCharge() > 0;
    }

    /**
     * 予約フォーム用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema
     */
    protected function buildReservationSchema(Schema $schema)
    {
        foreach ($this->getReservationFormGroups() as $formGroups) {
            foreach ($formGroups as $formGroup) {
                foreach ((array)$formGroup->get('form_items') as $formItem) {
                    /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                    $inputTypeItem = $formItem->getInputTypeItem();

                    if ($inputTypeItem instanceof InputInterface && $inputTypeItem->canInput()) {
                        foreach ((array)$inputTypeItem->getFieldsetInputKey() as $fieldsetInputKey) {
                            if ($formItem->get('input_type') === FormItem::INPUT_TYPE_EXPIRATION_DATE) {
                                $schema->addField($fieldsetInputKey . '_from', 'string');
                                $schema->addField($fieldsetInputKey . '_to', 'string');
                            } else {
                                $schema->addField($fieldsetInputKey, 'string');
                            }
                        }
                    }
                }
            }
        }
        $schema
            ->addField('reservations.reservation_type', 'string')
            ->addField('reservations.repeat_reservation', 'string')
            ->addField('reservations.date_to', 'string')
            ->addField('reservations.select_day_of_week', 'string')
            ->addField('reservations.day_of_week', 'string')
            ->addField('reservations.usage_timestamp_from', 'string')
            ->addField('reservations.validate_reserve_date_flg', 'string');

        return $schema;
    }

    /**
     * 予約フォーム用の値リストを生成
     *
     * @return array
     */
    protected function buildReservationFieldValueOptions()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $fieldValueOptions = [];
        if (!isset($this->reservationEntity) || $this->reservationEntity->isNew()) {
            $fieldValueOptions = $reservationsTable->getFieldValueOptionsForRegister();
        } else {
            $fieldValueOptions = $reservationsTable->getFieldValueOptionsForEdit();
        }

        return $fieldValueOptions;
    }

    /**
     * 会員の入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array
     */
    protected function filterReservationInputs($inputs)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $data = $inputs;
        if (isset($data['users']) && is_array($data['users'])) {
            $data['users'] = $usersTable->filterFormItemInputs(
                $data['users'],
                $this->getReservationFormGroups(FormGroup::FORM_TYPE_USER)
            );
        }
        if (isset($data['reservations']) && is_array($data['reservations'])) {
            $data['reservations'] = $reservationsTable->filterFormItemInputs(
                $data['reservations'],
                $this->getReservationFormGroups(FormGroup::FORM_TYPE_RESERVATION)
            );
        }

        return $data;
    }

    /**
     * 予約のバリデーション
     *
     * @param array $data データ
     * @return bool
     */
    protected function validateReservation(array $data)
    {
        $data = $this->filterReservationInputs($data);
        $this->setData($data);

        $options = [];
        if (!$this->isConfirm()) {
            $options['checkRules'] = true;
        }

        $this->createUserEntity($data, $options);
        $this->createReservationEntity($data, $options);
        if (
            isset($data['reservations']['repeat_reservation'])
            && $data['reservations']['validate_reserve_date_flg'] ===
                (string)Configure::readOrFail('Master.common.flg.on')
        ) {
            $this->validateRepeatReservation($data['reservations']);
        }

        if (!empty($this->getErrors())) {
            return false;
        }

        if ($this->getReservationEntity()->isNew() && (!empty($this->getContinuousParameter()))) {
            $this->generateContinuousKey();
        }

        return true;
    }

    /**
     * 会員のエンティティを生成
     *
     * @param array $data データ
     * @param array|null $options オプション
     * @return void
     */
    protected function createUserEntity($data, $options = null)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $reservationType = $this->getReservationParameter('reservation_type');
        if (
            isset($this->userEntity) && !$this->userEntity->isGuest()
            || ((string)$reservationType) === ((string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER)
        ) {
            return;
        }

        $userInputs = [];
        if (isset($data['users']) && is_array($data['users'])) {
            $userInputs = $data['users'];
        }

        $entityOptions = array_merge([
            'otherOptions' => [
                'formGroups' => $this->getReservationFormGroups(FormGroup::FORM_TYPE_USER),
                'parameters' => $this->getReservationParameter(),
                'isAdmin' => $this->isAdmin(),
                'isConfirm' => $this->isConfirm(),
                'password' => Hash::get($userInputs, 'password'),
            ],
        ], (array)$options);

        if (!isset($this->userEntity)) {
            $this->userEntity = $usersTable->newEntity($userInputs, $entityOptions);
        } else {
            $usersTable->patchEntity($this->userEntity, $userInputs, $entityOptions);
        }

        $entityErrors = $this->userEntity->getErrors();
        if (empty($entityErrors)) {
            $userData = $this->userEntity->toArray();
            unset($userData['user_additions']);
            $this->setData(array_merge($this->getData(), ['users' => $userData]));
        } else {
            $this->setErrors(Hash::merge($this->getErrors(), ['users' => $entityErrors]));
        }
    }

    /**
     * 予約のエンティティを生成
     *
     * @param array $data データ
     * @param array|null $options オプション
     * @return void
     */
    protected function createReservationEntity($data, $options = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $reservationInputs = [];
        if (isset($data['reservations']) && is_array($data['reservations'])) {
            $reservationInputs = $data['reservations'];
        }

        $continuousData = $this->getReservationEntities();
        if (!empty($continuousData)) {
            unset($continuousData[$this->getContinuousParameter('key')]);
        }

        $entityOptions = array_merge([
            'otherOptions' => [
                'formGroups' => $this->getReservationFormGroups(FormGroup::FORM_TYPE_RESERVATION),
                'parameters' => $this->getReservationParameter(),
                'event' => $this->eventEntity,
                'user' => $this->userEntity,
                'isAdmin' => $this->isAdmin(),
                'isConfirm' => $this->isConfirm(),
                'isChangeForm' => $this->isChangeForm(),
                'continuousData' => $continuousData,
            ],
        ], (array)$options);

        // 繰り返し予約の場合、予約可能日をまだ取得していない場合
        // 利用時間・利用開始時間が取得できない場合、複数日予約の予約日のバリデーションを行っていない場合
        if (
            $data
            && isset($reservationInputs['repeat_reservation'])
            && $reservationInputs['repeat_reservation'] === (string)Reservation::RESERVATION_TYPE_REPEAT_RESERVATION
            && !isset($reservationInputs['reserve_dates'])
            && isset($reservationInputs['usage_timestamp_from'])
            && isset($reservationInputs['usage_time'])
            && !isset($reservationInputs['validate_reserve_date_flg'])
        ) {
            [$reservationInputs, $repeatReservationInputs] =
                $this->createRepeatReservationData($data, $entityOptions);

            if ($repeatReservationInputs) {
                $this->createAndSetRepeatReservationEntity(
                    $reservationInputs,
                    $repeatReservationInputs,
                    $entityOptions
                );
                $entityOptions['otherOptions']['parameters']['usage_timestamp_from'] =
                    $reservationInputs['usage_timestamp_from'];
            }

            if (!isset($this->reservationEntity)) {
                $this->reservationEntity = $reservationsTable->newEntity($reservationInputs, $entityOptions);
            }
        } else {
            if (!isset($this->reservationEntity)) {
                $this->reservationEntity = $reservationsTable->newEntity($reservationInputs, $entityOptions);
            } else {
                $reservationsTable->patchEntity($this->reservationEntity, $reservationInputs, $entityOptions);
            }
        }

        $entityErrors = $this->reservationEntity->getErrors();
        if (empty($entityErrors)) {
            $reservationData = $this->reservationEntity->toArray();
            unset($reservationData['user']);
            unset($reservationData['reservation_additions']);
            unset($reservationData['reservation_options']);
            $this->setData(array_merge($this->getData(), ['reservations' => $reservationData]));
        } else {
            $this->setErrors(Hash::merge($this->getErrors(), ['reservations' => $entityErrors]));
        }
    }

    /**
     * フォーム変更処理かどうかの判定
     *
     * @return bool 判定結果
     */
    public function isChangeForm()
    {
        return $this->isChangeForm;
    }

    /**
     * フォーム変更処理のフラグを設定
     *
     * @param bool $isChangeForm フラグ
     * @return void
     */
    public function setChangeForm(bool $isChangeForm)
    {
        $this->isChangeForm = $isChangeForm;
    }

    /**
     * 繰り返し予約データの作成
     *
     * @param array $data 予約データ
     * @param array $entityOptions オプション
     * @return array
     */
    public function createRepeatReservationData($data, $entityOptions)
    {
        $reservationInputs = $data['reservations'];
        $from = new FrozenTime($reservationInputs['usage_timestamp_from']);
        $time = $from->format('H:i');
        $to = new FrozenTime($reservationInputs['date_to'] . $time);

        $allDate = new DatePeriod($from, new DateInterval('P1D'), $to->addDay());
        $event = $this->getEventEntity();

        $reservationInput = [];
        $repeatReservationInputs = [];
        if ($event) {
            foreach ($allDate as $date) {
                // 曜日指定がある場合、指定曜日の時のみ処理を実行
                if ($reservationInputs['select_day_of_week']) {
                    if (!DateTimeUtility::isWithinWeekHoliday($date, (array)$reservationInputs['day_of_week'])) {
                        continue;
                    }
                }

                $usageTimestampFrom = $date->format('Y/m/d H:i');

                $reservationInput['reservations'] = $reservationInputs;
                $reservationInput['reservations']['usage_timestamp_from'] = $usageTimestampFrom;
                //　フラグセット
                $reservationInput['reservations']['validate_reserve_date_flg'] =
                    (string)Configure::read('Master.common.flg.on');

                $reservationForm = new ReservationForm();
                $reservationForm->setReservationParameter([
                    'event_id' => $entityOptions['otherOptions']['event']->get('id'),
                    'usage_timestamp_from' => $usageTimestampFrom,
                ]);

                if (
                    $reservationForm->validateReservationParameter()
                    && $reservationForm->validateReservation($reservationInput)
                ) {
                    $reservationInputs['reserve_dates'][] = $usageTimestampFrom;
                    $repeatReservationInput = $reservationInputs;
                    $repeatReservationInput['usage_timestamp_from'] = $usageTimestampFrom;
                    $repeatReservationInputs[] = $repeatReservationInput;
                } else {
                    $reservationInputs['can_not_reserve_dates'][] = $usageTimestampFrom;
                }
            }
            if (!$reservationInputs['date_to']) {
                 $this->validateRepeatReservation($reservationInputs);
            }
            //フラグ解除
            $reservationInput['reservations']['validate_reserve_date_flg'] = null;
        }

        return [$reservationInputs, $repeatReservationInputs];
    }

    /**
     * 繰り返し予約データのエンティティを作成
     *
     * @param array $reservationInputs 予約データ
     * @param array $repeatReservationInputs 繰り返し予約データ
     * @param array  $entityOptions オプション
     * @return void
     */
    public function createAndSetRepeatReservationEntity($reservationInputs, $repeatReservationInputs, $entityOptions)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $repeatReservation = [];
        $repeatReservationData = [];
        $i = 0;
        foreach ($repeatReservationInputs as $repeatReservationInput) {
            $entityOptions['otherOptions']['parameters']['usage_timestamp_from'] =
                $repeatReservationInput['usage_timestamp_from'];
            $repeatReservationInput['reserve_dates'] = $reservationInputs['reserve_dates'] ?? null;
            $repeatReservationInput['can_not_reserve_dates'] = $reservationInputs['can_not_reserve_dates'] ?? null;
            $this->repeatReservationDataEntity =
                $reservationsTable->newEntity($repeatReservationInput, $entityOptions);
            $entityErrors = $this->repeatReservationDataEntity->getErrors();
            if (empty($entityErrors)) {
                $repeatReservation['reservations'] = $this->repeatReservationDataEntity->toArray();
                $repeatReservationData[$i] = $repeatReservation;
            }
            $i++;
        }
        $this->setData(array_merge($this->getData(), ['repeatReservations' => $repeatReservationData]));
    }

    /**
     * 繰り返し予約のバリデーション
     *
     * @param array $data 予約データ
     * @return void
     */
    public function validateRepeatReservation($data)
    {
        $validator = new Validator();

        $types = array_map('strval', array_keys(Configure::readOrFail('Master.reservation.repeatReservationType')));
        $validate_reserve_date_flg = !isset($data['validate_reserve_date_flg']);
        $selectDayOfWeek = isset($data['select_day_of_week'])
            && ($data['select_day_of_week'] === (string)Configure::readOrFail('Master.common.flg.on'));

        $validator
            ->requirePresence('repeat_reservation', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('repeat_reservation', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('repeat_reservation', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', $types],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'canNotReserve' => [
                    'rule' => function ($value, $context) {
                        return !($value === (string)Reservation::RESERVATION_TYPE_REPEAT_RESERVATION
                            && empty($context['data']['reserve_dates']));
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NO_AVAILABLE_DATE_IN_INPUTED_TERM),
                    'on' => function ($context) use ($validate_reserve_date_flg) {
                        return $validate_reserve_date_flg;
                    },
                ],
            ]);

        $validator
            ->requirePresence('date_to', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->notEmptyDate('date_to', __(Message::ERROR_NOT_EMPTY_SELECT))
            ->add('date_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => function ($value, $context) {
                        $from = $context['data']['usage_timestamp_from'];
                        $to = $value;
                        $fromDate = new FrozenDate($from);
                        $toDate = new FrozenDate($to);

                        return $fromDate <= $toDate;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
                'withInOneYear' => [
                    'rule' => function ($value, $context) {
                        $from = $context['data']['usage_timestamp_from'];
                        $to = $value;
                        $fromDate = new FrozenDate($from);
                        $toDate = new FrozenDate($to);
                        $diff = $fromDate->diff($toDate)->days;

                        return $diff < ReservationsTable::OVER_YEAR_DATES;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DAYS),
                ],
            ]);

        $validator
            ->requirePresence('select_day_of_week', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('select_day_of_week', __(Message::ERROR_NOT_EMPTY_SELECT), true)
            ->add('select_day_of_week', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence(
                'day_of_week',
                function ($context) use ($selectDayOfWeek) {
                    return isset($context['data']['select_day_of_week'])
                        && $selectDayOfWeek;
                },
                __(Message::ERROR_NOT_EMPTY_SELECT)
            )
            ->notEmptyString(
                'day_of_week',
                __(Message::ERROR_NOT_EMPTY_SELECT),
                function ($context) use ($selectDayOfWeek) {
                    return isset($context['data']['select_day_of_week'])
                        && $selectDayOfWeek;
                }
            )
            ->add('day_of_week', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'on' => function ($context) use ($selectDayOfWeek) {
                        return $selectDayOfWeek;
                    },
                ],
                'inList' => [
                    'rule' => ['inList', array_keys(Configure::readOrFail('Master.common.week'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                    'on' => function ($context) use ($selectDayOfWeek) {
                        return $selectDayOfWeek;
                    },
                ],
            ]);

        $errors = $validator->validate($data);
        if (!empty($errors)) {
            $this->setErrors(Hash::merge($this->getErrors(), ['reservations' => $errors]));
        }
    }
}
