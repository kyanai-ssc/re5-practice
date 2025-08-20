<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\Reservation;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\Table\ReservationsTable;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;

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
            ->addField('reservations.reservation_type', 'string');

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

        if (!isset($this->reservationEntity)) {
            $this->reservationEntity = $reservationsTable->newEntity($reservationInputs, $entityOptions);
        } else {
            $reservationsTable->patchEntity($this->reservationEntity, $reservationInputs, $entityOptions);
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
}
