<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\ReservationStatus;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * AkerunUserId class.
 */
class AkerunUserId extends AbstractInputTypeItem implements
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    /**
     * @var string
     */
    protected $tableName = 'user_smart_locks';

    /**
     * @var string
     */
    protected $columnName = 'smart_lock_user_id';

    public const AKERUN_USER_ID_MAX = 100;

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        if (!$this->canUseAkerun()) {
            $this->displayType['canDisplay'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $userSmartLock = Hash::get($options, 'user.user_smart_lock');
        if (!isset($userSmartLock, $userSmartLock['smart_lock_user_id'])) {
            return null;
        }

        return $userSmartLock['smart_lock_user_id'];
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::AKERUN_USER_ID_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::AKERUN_USER_ID_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $value = Hash::get($inputs, $this->getSearchInputKey());
        if (isset($value) && $value !== '') {
            $parameter = $this->driverExpression()->escapeLike($value);
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() => $parameter,
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $akerunUserIdValidator = new KuchenValidator();

        $validator
            ->requirePresence($this->getEntityKey(), false)
            ->allowEmptyString($this->getEntityKey())
            ->addNested($this->getEntityKey(), $akerunUserIdValidator);

        $akerunUserIdValidator
            ->requirePresence($this->getColumnName(), false)
            ->allowEmptyString(
                $this->getColumnName(),
                __(Message::ERROR_NOT_EMPTY),
                function ($context) {
                    // 新規か変更前も空の場合のみ、空を許可する
                    $akerunUserId = $this->getAkerunUserId();

                    return is_null($akerunUserId) || $akerunUserId === '';
                }
            )
            ->add($this->getColumnName(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::AKERUN_USER_ID_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::AKERUN_USER_ID_MAX),
                ],
                'errorIsReserveFuture' => [
                    'rule' => function ($value) {
                        // 未来にステータスタイプが「確定」の予約がある場合は変更不可のチェック
                        $akerunUserId = $this->getAkerunUserId();
                        if (!$this->getConfig('userId') || (string)$value === (string)$akerunUserId) {
                            // 新規登録もしくは変更なしはチェックしない
                            return true;
                        }

                        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
                        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
                        $statuses =
                            $reservationStatusesTable->getGroupingStatusType([ReservationStatus::STATUS_TYPE_FIXED]);
                        /** @var \App\Model\Table\ReservationsTable $reservationTable */
                        $reservationTable = $this->getTableLocator()->get('Reservations');

                        $count = $reservationTable->find('count', [
                            'inputs' => [
                                'user_id' => (int)$this->getConfig('userId'),
                                'usage_timestamp_future' => true,
                                'status' => array_keys($statuses),
                            ],
                        ])
                        ->limit(1)
                        ->count();

                        return $count === 0;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IS_RESERVE_FUTURE),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function canCsvOutput()
    {
        return $this->canUseAkerun();
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['users']['user_smart_lock']['smart_lock_user_id'] = $data;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.values');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'user_smart_lock.smart_lock_user_id') ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'user_smart_lock_user_id';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'user.user_smart_lock.smart_lock_user_id');
    }

    /**
     * @inheritDoc
     */
    public function getFieldsetInputKey()
    {
        $key = $this->getEntityKey();
        $columnName = $this->getColumnName();
        if (empty($columnName)) {
            return null;
        }

        return 'users.' . $key . '.' . $columnName;
    }

    /**
     * Akerunの利用可否を判定
     *
     * @return bool
     */
    protected function canUseAkerun()
    {
        $smartLock = new SmartLockLinkage();

        return $smartLock->useAkerun();
    }

    /**
     * エンティティで利用されるキー（tableNameの末尾のsを除いた値）を取得
     *
     * @return string
     */
    protected function getEntityKey()
    {
        $tableName = $this->getTableName();
        if (!isset($tableName)) {
            return '';
        }

        return rtrim($tableName, 's');
    }

    /**
     * 登録済の Akerun ユーザー ID を取得する
     *
     * @return string|null Akerun ユーザー ID
     */
    protected function getAkerunUserId(): ?string
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Entity\User $user */
        $user = $usersTable->find('smartLock')
            ->where(['Users.id' => (int)$this->getConfig('userId')])
            ->first();

        return $this->getDetailValue(['user' => $user]);
    }

    /**
     * @inheritDoc
     */
    public function canInput()
    {
        if (!$this->canDisplay()) {
            return false;
        }
        if (!isset($this->displayType['canInput'])) {
            return false;
        }

        return $this->displayType['canInput'] && ($this->isAdmin() || is_null($this->getAkerunUserId()));
    }
}
