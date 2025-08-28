<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\AutoReplyMailHistory;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\User;
use App\Model\Entity\UserAddition;
use App\Model\Entity\UserSmartLock;
use App\Model\ImportableTableInterface;
use App\Model\InputType\Item\Type\AdditionTypeInterface;
use App\Model\InputType\Item\Type\InputInterface;
use App\Utility\SmartLock\SmartLockLinkage;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersTable extends AppTable implements ImportableTableInterface
{
    /**
     * CSV出力時の1回の取得件数
     */
    public const CSV_PAGEVIEW = 5000;

    public const ONLY_ADMIN_DISPLAY_TYPE = 3;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('UserAuthorities', [
            'foreignKey' => 'user_authority_id',
            'joinType' => 'INNER',
        ]);
        $this->hasOne('UserLoginHistories', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('AutoReplyMailHistories', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('BounceMailHistories', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('Inquiries', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('MailDeliveryHistories', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Reservations', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserAdditions', [
            'foreignKey' => 'user_id',
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('UserPasswordReminderTokens', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('WaitingCancellations', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasOne('UserSmartLocks', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
    }

    /**
     * パラメータのバリデーション
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationParameter(Validator $validator)
    {
        $validator
            ->requirePresence('user_authority_id', false)
            ->allowEmptyString('user_authority_id', __(Message::ERROR_NOT_EMPTY), true)
            ->add('user_authority_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // プラン上限チェック
        $rules->addCreate(
            function ($entity) {
                if (!($entity instanceof User)) {
                    throw new CakeException();
                }

                if ($entity->isGuest()) {
                    return true;
                }
                if (!$this->checkPlanRestriction()) {
                    return false;
                }

                return true;
            },
            'planRestriction',
            [
                'errorField' => 'user_error',
                'message' => __(Message::PLAN_USER_RESTRICTION_OVER),
            ]
        );

        // ログインID重複チェック
        $rules->add(
            function ($entity) {
                if (!empty($entity->getError('login_id'))) {
                    return true;
                }
                if ((string)$entity->get('login_id') === '') {
                    return true;
                }

                if (!($entity instanceof User)) {
                    throw new CakeException();
                }

                if ($entity->isGuest()) {
                    return true;
                }
                $query = $this->find('loginIdIsUnique', [
                    'inputs' => [
                        'login_id' => $entity->get('login_id'),
                        'exclude_id' => $entity->get('id'),
                    ],
                ]);
                if ($query->count() > 0) {
                    return false;
                }

                return true;
            },
            'loginIdIsUnique',
            [
                'errorField' => 'login_id',
                'message' => __(Message::ERROR_EXISTS),
            ]
        );

        // メールアドレス重複チェック
        $rules->add(
            function ($entity) {
                if (!empty($entity->getError('mail'))) {
                    return true;
                }
                if ((string)$entity->get('mail') === '') {
                    return true;
                }

                if (!($entity instanceof User)) {
                    throw new CakeException();
                }

                if ($entity->isGuest()) {
                    return true;
                }
                $query = $this->find('mailIsUnique', [
                    'inputs' => [
                        'mail' => $entity->get('mail'),
                        'exclude_id' => $entity->get('id'),
                    ],
                ]);
                if ($query->count() > 0) {
                    return false;
                }

                return true;
            },
            'mailIsUnique',
            [
                'errorField' => 'mail',
                'message' => __(Message::ERROR_EXISTS),
            ]
        );

        // 削除時の予約存在チェック
        $rules->addDelete(
            function ($entity) {
                if (!($entity instanceof User)) {
                    throw new CakeException();
                }

                return $entity->canDelete();
            },
            'notExistsReserve',
            [
                'errorField' => 'withdrawal_flg',
                'message' => __(Message::ERROR_DELETE_USER),
            ]
        );

        return $rules;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $searchInputKey = Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey');

        $this->searchManager()
            ->value($searchInputKey[AdminSearchItem::ITEM_USER_ID], [
                'fields' => 'id',
                'multiValue' => true,
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_GUEST_FLG], [
                'fields' => 'guest_flg',
                'multiValue' => true,
            ])
            ->value($searchInputKey[AdminSearchItem::ITEM_WITHDRAWAL_FLG], [
                'fields' => 'withdrawal_flg',
                'multiValue' => true,
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_USER_INS_TIMESTAMP] . '.from', [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $date = new FrozenDate($args[$filter->name()]);
                        $query->where([
                            'Users.created >=' => $date->format('Y-m-d H:i:s'),
                        ]);
                    }
                },
            ])
            ->callback($searchInputKey[AdminSearchItem::ITEM_USER_INS_TIMESTAMP] . '.to', [
                'callback' => function ($query, $args, $filter) {
                    if (isset($args[$filter->name()])) {
                        $date = new FrozenDate($args[$filter->name()]);
                        $date = $date->addDays(1);
                        $query->where([
                            'Users.created <' => $date->format('Y-m-d H:i:s'),
                        ]);
                    }
                },
            ]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $query->select([
            'id',
            'user_authority_id',
            'login_id',
            'mail',
            'guest_flg',
            'withdrawal_flg',
            'expiration_date_from',
            'expiration_date_to',
            'created',
            'modified',
            'guest_reservation_id' => 'GuestReservations.id',
        ]);
        $query->join([
            'GuestReservations' => [
                'table' => 'reservations',
                'type' => 'LEFT',
                'conditions' => [
                    'Users.id = GuestReservations.user_id',
                    'Users.guest_flg' => User::GUEST_FLG_ON,
                ],
            ],
        ]);
        $query->contain([
            'UserAdditions' => [
                'fields' => [
                    'id',
                    'user_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
            'UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        // 会員フォーム項目の検索
        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_USER) as $formItems) {
            foreach ($formItems as $formItem) {
                $formItem->getInputTypeItem()->buildSearchQuery($query, Hash::get($options, 'inputs', []));
            }
        }

        // 予約項目の検索
        $reservationQuery = $reservationsTable->selectQuery();
        $reservationQuery->select(['Reservations.user_id']);
        $reservationQuery->join([
            'Events' => [
                'table' => 'events',
                'type' => 'INNER',
                'conditions' => [
                    'Reservations.event_id = Events.id',
                ],
            ],
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => [
                    'Events.label_id = Labels.id',
                ],
            ],
        ]);
        $labelsTable->joinQuery($reservationQuery, 'Labels');
        $reservationQuery = $reservationsTable->callFinder('search', $reservationQuery, [
            'search' => Hash::get($options, 'inputs', []),
        ]);
        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItems) {
            foreach ($formItems as $formItem) {
                $formItem->getInputTypeItem()->buildSearchQuery($reservationQuery, Hash::get($options, 'inputs', []));
            }
        }
        $reservationWhere = $reservationQuery->clause('where');
        if (isset($reservationWhere)) {
            $query->where([
                'Users.id IN' => $reservationQuery,
            ]);
        }

        $checked = Hash::get($options, 'checked');
        if (is_array($checked) && !isset($checked['allCheck'])) {
            $query->where([
                'Users.id IN' => $checked,
            ]);
        }

        $mailDeliveryFlg = Hash::get($options, 'mailDelivery');
        if (isset($mailDeliveryFlg) && $mailDeliveryFlg) {
            $query->where([
                'Users.mail IS NOT' => null,
            ]);
        }

        $sort = Hash::get($options, 'inputs.sort', 'Users.id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query->order([
                $sort => $direction,
            ] + [
                'Users.id' => $direction,
            ], true);

        if (Hash::get($options, 'bufferOff', false)) {
            $query->disableBufferedResults();
        }

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 編集時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_authority_id',
            'login_id',
            'mail',
            'guest_flg',
            'withdrawal_flg',
            'expiration_date_from',
            'expiration_date_to',
            'created',
            'modified',
        ]);

        $query->contain([
            'UserAdditions',
            'UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);
        $query->where([
            'Users.guest_flg' => User::GUEST_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * 退会時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWithdraw(Query $query, array $options)
    {
        return $this->callFinder('edit', $query, $options);
    }

    /**
     * 削除時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);

        return $query;
    }

    /**
     * 権限更新時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUpdateAuthority(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_authority_id',
        ]);
        $query->where([
            'Users.guest_flg' => User::GUEST_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * メール送信者
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMailDeliveryUsers(Query $query, array $options)
    {
        $target = $options['target'];

        $query->find('searchList', $target)
            ->select([
                'Users.id',
                'Users.mail',
                'BounceMails.send_exclude_flg',
            ])
            ->where('Users.mail IS NOT NULL')
            ->leftJoin(['BounceMails' => 'bounce_mails'], ['BounceMails.mail' => 'Users.mail']);

        $query->disableBufferedResults();

        return $query;
    }

    /**
     * メール配信履歴のユーザーファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDeliveryUserList(Query $query, array $options)
    {
        $query = $this->findSearchList($query, $options);
        $query->contain([
            'MailDeliveryHistories' => [
                'fields' => [
                    'id',
                    'mail_delivery_id',
                    'user_id',
                    'send_status',
                ],
                'queryBuilder' => function ($mailDeliveryHistoryQuery) use ($options) {
                    $mailDeliveryHistoryQuery->where([
                        'MailDeliveryHistories.mail_delivery_id' => $options['mailDeliveryId'],
                    ]);

                    return $mailDeliveryHistoryQuery;
                },
            ],
        ]);
        $query->matching('MailDeliveryHistories', function ($q) use ($options) {
            return $q->where(['mail_delivery_id IN' => $options['mailDeliveryId']]);
        });

        return $query;
    }

    /**
     * 予約時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservation(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_authority_id',
            'login_id',
            'mail',
            'guest_flg',
            'withdrawal_flg',
            'expiration_date_from',
            'expiration_date_to',
            'created',
            'modified',
        ]);

        $query->contain([
            'UserAdditions' => [
                'fields' => [
                    'id',
                    'user_id',
                    'form_item_id',
                    'value',
                ],
            ],
            'UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * プラン制限チェックのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findPlanRestriction(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);

        $query->where([
            'Users.guest_flg' => User::GUEST_FLG_OFF,
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * ログインID重複チェックのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findLoginIdIsUnique(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);

        $query->where([
            'Users.login_id' => Hash::get($options, 'inputs.login_id'),
            'Users.guest_flg' => User::GUEST_FLG_OFF,
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);

        $excludeId = Hash::get($options, 'inputs.exclude_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Users.id NOT IN' => (array)$excludeId,
            ]);
        }

        return $query;
    }

    /**
     * メールアドレス重複チェックのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMailIsUnique(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);

        $query->where([
            'Users.mail' => Hash::get($options, 'inputs.mail'),
            'Users.guest_flg' => User::GUEST_FLG_OFF,
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);

        $excludeId = Hash::get($options, 'inputs.exclude_id');
        if (is_scalar($excludeId) && ((string)$excludeId !== '') || is_array($excludeId) && !empty($excludeId)) {
            $query->where([
                'Users.id NOT IN' => (array)$excludeId,
            ]);
        }

        return $query;
    }

    /**
     * 台帳指定のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendar(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);

        $query->where([
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);
        if (!Hash::get($options, 'selectCalendar', false)) {
            $query->where([
                'Users.guest_flg' => User::GUEST_FLG_OFF,
            ]);
        }

        return $query;
    }

    /**
     * パラメータのデータを取得
     *
     * @param array $parameters パラメータ
     * @param \App\Model\Entity\User|null $user エンティティ
     * @return array
     */
    public function getUserParametersData(array $parameters, ?User $user = null)
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $returnErrors = function ($errors) {
            return ['errors' => ['users' => $errors]];
        };
        $displayErrors = [];

        $validator = $this->validationParameter(new KuchenValidator());
        $errors = $validator->validate($parameters);
        if (!empty($errors)) {
            return call_user_func($returnErrors, $errors);
        }

        $isAdmin = $this->commonData()->existsAdminLoginData();

        // 会員権限
        $userAuthorityId = Hash::get($parameters, 'user_authority_id');
        if (
            !isset($userAuthorityId) && $isAdmin
            || isset($userAuthorityId) && !$userAuthoritiesTable->exists(['id' => $userAuthorityId])
        ) {
            $userAuthorityId = null;
            $displayErrors = [
                'users' => [
                    'user_authority_id' => [__(Message::ERROR_NOT_EXISTS)],
                ],
            ];
        }
        if (((string)$userAuthorityId) === '') {
            if (isset($user)) {
                $userAuthorityId = $user->get('user_authority_id');
            } else {
                $userAuthorityId = $userAuthoritiesTable->getDefaultAuthority()->get('id');
            }
        }
        $guestAuthorityId = $userAuthoritiesTable->getGuestAuthority()->get('id');
        if ((!isset($user) || !$user->isGuest()) && ((string)$userAuthorityId) === ((string)$guestAuthorityId)) {
            return call_user_func($returnErrors, [
                'user_authority_id' => [__(Message::ERROR_INVALID_VALUE)],
            ]);
        }

        $result = [
            'parameters' => [
                'user_authority_id' => $userAuthorityId,
            ],
        ];
        if (!empty($displayErrors)) {
            $result['displayErrors'] = $displayErrors;
        }

        return $result;
    }

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        $parameters = Hash::get($options, 'otherOptions.parameters');
        if (!empty($parameters)) {
            $data->offsetSet('user_authority_id', $parameters['user_authority_id']);
        }

        if (Hash::get($options, 'validate', true) === true) {
            $formGroups = Hash::get($options, 'otherOptions.formGroups', []);
            $isConfirm = Hash::get((array)$options, 'otherOptions.isConfirm', false);

            if ($isConfirm) {
                $data->offsetSet('password', null);
                $data->offsetSet('password_confirm', null);
            }

            $this->setValidator(
                'formItems',
                $this->createFormItemValidators($this->createValidator('default'), $formGroups)
            );
            $options->offsetSet('validate', 'formItems');
        }

        $smartLock = new SmartLockLinkage();
        if ($smartLock->useAkerun()) {
            $options['associated'] = ['UserSmartLocks'];
        }
    }

    /**
     * フォーム項目の入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @param array $formGroups フォームグループ
     * @return array
     */
    public function filterFormItemInputs($inputs, $formGroups)
    {
        $additionInputs = Hash::get($inputs, 'addition_values', []);
        unset($inputs['addition_values']);

        foreach ($formGroups as $formGroup) {
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                $inputTypeItem = $formItem->getInputTypeItem();

                if ($inputTypeItem instanceof InputInterface) {
                    if ($inputTypeItem instanceof AdditionTypeInterface) {
                        $additionInputs = $inputTypeItem->filterInputs($additionInputs);
                    } else {
                        $inputs = $inputTypeItem->filterInputs($inputs);
                    }
                }
            }
        }
        $inputs['addition_values'] = $additionInputs;

        return $inputs;
    }

    /**
     * フォーム項目のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $formGroups フォームグループ
     * @return \Cake\Validation\Validator
     */
    protected function createFormItemValidators($validator, $formGroups)
    {
        $additionValidator = new KuchenValidator();

        foreach ($formGroups as $formGroup) {
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                $inputTypeItem = $formItem->getInputTypeItem();

                if ($inputTypeItem instanceof InputInterface && $inputTypeItem->canInput()) {
                    if ($inputTypeItem instanceof AdditionTypeInterface) {
                        $additionValidator = $inputTypeItem->buildFieldsetValidator($additionValidator);
                    } else {
                        $validator = $inputTypeItem->buildFieldsetValidator($validator);
                    }
                }
            }
        }

        $validator->addNested('addition_values', $additionValidator);
        $validator->requirePresence('addition_values', false);
        $validator->allowEmptyString('addition_values', __(Message::ERROR_NOT_EMPTY), true);

        return $validator;
    }

    /**
     * Model.afterMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterMarshal(
        EventInterface $event,
        EntityInterface $entity,
        ArrayObject $data,
        ArrayObject $options
    ) {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        if (empty($entity->getError('user_authority_id'))) {
            $guestAuthority = $userAuthoritiesTable->getGuestAuthority();
            if ((string)$entity->get('user_authority_id') === (string)$guestAuthority->get('id')) {
                $entity->set('guest_flg', User::GUEST_FLG_ON);
            }
        }

        if ($entity->hasErrors()) {
            return;
        }
        if (!($entity instanceof User)) {
            throw new CakeException();
        }

        if ($entity->has('user_authority_id')) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
            $userAuthoritiyId = $entity->get('user_authority_id');
            if ($entity->isNew() && !$isAdmin) {
                // 公開側からの会員登録時はゲスト権限の項目が表示される仕様なので、フォーム項目に関する処理で使う権限はそれに合わせる。
                $userAuthoritiyId = $userAuthoritiesTable->getGuestAuthority()->get('id');
            }

            $userAuthority = $userAuthoritiesTable->get($userAuthoritiyId);
            $entity->setUserAuthorityEntity($userAuthority);

            // 表示パターン設定で使用されていない値を削除する
            $entity->unsetUnusedValues('UserAdditions');
        }

        $isConfirm = Hash::get($options, 'otherOptions.isConfirm', false);
        $password = Hash::get($options, 'otherOptions.password');
        if (((string)$password) !== '') {
            if (!$isConfirm) {
                $entity->set('plain_password', $password);
            } else {
                $entity->set('password', $password, ['setter' => false]);
            }
        }

        if ($entity->isNew()) {
            $defaults = [
                'guest_flg' => User::GUEST_FLG_OFF,
                'withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
            ];
            foreach ($defaults as $key => $value) {
                if ((string)$entity->get($key) === '') {
                    $entity->set($key, $value);
                }
            }
        }
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\UserLoginHistoriesTable $userLoginHistoriesTable */
        $userLoginHistoriesTable = $this->getTableLocator()->get('UserLoginHistories');
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        if (!($entity instanceof User)) {
            throw new CakeException();
        }

        //繰り返し予約フラグの表示パターンを取得
        $userAuthorityId = $entity->get('user_authority_id');

        $userAuthority = $userAuthoritiesTable->find()
            ->select(['form_pattern_id'])
            ->where(['id' => $userAuthorityId])
            ->enableHydration(true)
            ->firstOrFail();
        /** @var \App\Model\Entity\UserAuthority $userAuthority */
        $formPatternId = $userAuthority->get('form_pattern_id');

        $formPatternDisplayType = $formPatternDisplayTypesTable->find()
            ->select(['display_type'])
            ->where(['form_pattern_id' => $formPatternId,
                'form_item_id' => FormItem::FORM_ITEMS_ID_REPEAT_RESERVATION_FLG,
            ])
            ->enableHydration(true)
            ->firstOrFail();
        /** @var \App\Model\Entity\FormPatternDisplayType $formPatternDisplayType*/
        $displayType = $formPatternDisplayType->get('display_type');

        // 公開側から会員登録で「繰り返し予約フラグ」の表示パターンが「③表示する：管理画面のみ」場合、不可で登録
        if (
            $entity->isNew()
            && !($this->commonData()->existsAdminLoginData())
            && $displayType === static::ONLY_ADMIN_DISPLAY_TYPE
        ) {
            $item = $entity->addition_values ?? [];
            $item[Configure::readOrFail('Setting.formItemAdditionValues.repeatReservationFlg')]
                = UserAddition::REPEAT_RESERVATION_FLG_OFF;
            $entity->set('addition_values', $item);
        }

        // ログイン履歴の生成
        if ($entity->isNew()) {
            $entity->set('user_login_history', $userLoginHistoriesTable->createDefaultData());
        }

        if ((string)$entity->get('password') === '') {
            $entity->setDirty('password', false);
        }
        if ($entity->isDirty('password')) {
            $entity->set('password_modify_timestamp', clone $this->commonData()->getNowDateTime());
            if (!$entity->isNew()) {
                // ログイン履歴の更新
                $userLoginHistoriesTable->updateOnPasswordChange($entity->get('id'));
            }
        }

        // 自動返信メール履歴の生成
        $autoReplyMailHistory = null;
        if ((string)Hash::get($options, 'mailSendFlg') === (string)Configure::readOrFail('Master.common.flg.on')) {
            $cryptPassword = Hash::get($options, 'cryptPassword');
            if (((string)$cryptPassword) !== '') {
                $entity->set('crypt_password', $cryptPassword);
            }

            $autoReplyMailHistory = $autoReplyMailHistoriesTable->generateDataForUser($entity);
        }
        if (isset($autoReplyMailHistory)) {
            $mailData = [
                'user' => $entity,
            ];
            if (!$entity->isNew()) {
                try {
                    /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                    $mailData['oldUser'] = $this->get($entity->get('id'), [
                        'finder' => 'edit',
                    ]);
                } catch (RecordNotFoundException $e) {
                    throw new CakeException();
                }
            }
            $autoReplyMailHistory->setDataEntity($mailData);

            $entity->set('auto_reply_mail_histories', [$autoReplyMailHistory]);
        } else {
            $entity->set('auto_reply_mail_histories', null);
        }

        // 操作ログ
        $saveOperation = Hash::get($options, 'saveOperation');
        if (!empty($saveOperation) && !$entity->isGuest()) {
            $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
        }
    }

    /**
     * @inheritDoc
     */
    public function save(EntityInterface $entity, $options = [])
    {
        // ロック取得
        $lockLoginId = null;
        if ($entity->isDirty('login_id') && ((string)$entity->get('login_id')) !== '') {
            $lockLoginId = $this->getLockForLoginId($entity->get('login_id'));
        }
        $lockMail = null;
        if (
            $entity->isDirty('mail') && ((string)$entity->get('mail')) !== ''
            && ((string)$entity->get('guest_flg')) === ((string)User::GUEST_FLG_OFF)
        ) {
            $lockMail = $this->getLockForMail($entity->get('mail'));
        }

        try {
            $result = $this->getConnection()->transactional(function () use ($entity, $options) {
                $isNew = $entity->isNew();
                $originalUser = null;
                if (!$isNew) {
                    /** @var \Cake\Datasource\EntityInterface|null $originalUser */
                    $originalUser = $this->find('SmartLock')
                        ->where(['Users.id' => $entity->get('id')])
                        ->first();
                }
                $result = parent::save($entity, $options);

                // スマートロック連携
                if ($result) {
                    $this->smartLockLinkage($entity, $isNew, $originalUser);
                }

                return $result;
            });
        } finally {
            // ロック解放
            if (isset($lockLoginId)) {
                $this->releaseLockForLoginId($lockLoginId);
            }
            if (isset($lockMail)) {
                $this->releaseLockForMail($lockMail);
            }
        }

        // メール送信
        if (!Hash::get($options, 'forReservation', false) && $result) {
            $this->sendUserMail($entity);
        }

        return $result;
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        // オプトイントークン削除
        $optinToken = Hash::get($options, 'optinToken');
        if (isset($optinToken)) {
            /** @var \App\Model\Table\OptinTokensTable $optinTokensTable */
            $optinTokensTable = $this->getTableLocator()->get('OptinTokens');

            $optinTokensTable->deleteOrFail($optinToken);
        }
    }

    /**
     * Model.beforeDeleteイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
    }

    /**
     * 権限を更新
     *
     * @param \Cake\Datasource\EntityInterface $user 会員
     * @param int $userAuthorityId 権限
     * @param array|null $saveOperation 操作ログ
     * @return void
     */
    public function updateAuthority(EntityInterface $user, int $userAuthorityId, ?array $saveOperation = null)
    {
        $user->clean();
        $user->set('user_authority_id', $userAuthorityId);
        $this->save($user, [
            'saveOperation' => $saveOperation,
        ]);
    }

    /**
     * 会員を退会
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param array $options オプション
     * @return bool
     */
    public function withdraw(EntityInterface $entity, array $options = [])
    {
        $entity->clean();
        $entity->set('withdrawal_flg', User::WITHDRAWAL_FLG_ON);

        $this->checkRules($entity, RulesChecker::DELETE, $options);
        $options['checkRules'] = false;

        if (!$this->save($entity, $options)) {
            return false;
        }

        return true;
    }

    /**
     * チェックしたユーザーの情報を取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCheckUsers(Query $query, array $options)
    {
        $checked = Hash::get($options, 'checked', []);
        $condition = Hash::get($options, 'condition', []);

        if (isset($checked['allCheck'])) {
            $query = $query->find('searchList', [
                'inputs' => $condition,
            ]);
        } else {
            $query = $query->find('searchList', [
                'checked' => $checked,
            ]);
        }
        $query->where(['Users.guest_flg' => User::GUEST_FLG_OFF]);

        $query->where(function ($expression) {
            $reservationsQuery = $this->getAssociation('Reservations')->find('count', [
                'inputs' => [
                    'usage_timestamp_future' => true,
                    'not_cancel' => true,
                ],
            ]);
            $reservationsQuery->where([
                'Reservations.user_id = Users.id',
            ]);
            $expression->notExists($reservationsQuery);

            return $expression;
        });

        return $query;
    }

    /**
     * 複数会員を削除
     *
     * @param array $checked チェック情報
     * @param array $condition 検索条件
     * @param array|null $saveOperation 操作ログ
     * @return bool
     */
    public function deleteUsers(array $checked, array $condition, ?array $saveOperation = null)
    {
        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);

        $query = $this->find('checkUsers', [
            'checked' => $checked,
            'condition' => $condition,
        ]);

        return $this->deleteData($query, [
            'saveOperation' => $saveOperation,
        ]);
    }

    /**
     * 予約が存在しない非会員データを削除
     *
     * @param int|null $userId 会員ID
     * @return void
     */
    public function deleteNoReservationGuest($userId = null)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $notExistsQuery = $reservationsTable->find();
        $notExistsQuery->select([1]);
        $notExistsQuery->where([
            'Reservations.user_id = Users.id',
        ]);

        $condition = [
            'guest_flg' => User::GUEST_FLG_ON,
            $notExistsQuery->newExpr()->notExists($notExistsQuery),
        ];
        if (isset($userId)) {
            $condition['id'] = $userId;
        }

        $this->deleteAll($condition);
    }

    /**
     * 会員のメールを送信
     *
     * @param \Cake\Datasource\EntityInterface $user 会員
     * @return void
     */
    public function sendUserMail($user)
    {
        $this->executeSafe(function () use ($user) {
            if (!$user->has('auto_reply_mail_histories')) {
                return;
            }

            /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
            $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

            foreach ($user->get('auto_reply_mail_histories') as $autoReplyMailHistory) {
                $autoReplyMailHistoriesTable->sendAutoReplyMail($autoReplyMailHistory, true, [], null, true);
            }
        });
    }

    /**
     * 会員フォームの項目一覧を取得
     *
     * @param int $userAuthorityId 会員権限ID
     * @param array $options オプション引数
     * @return array 入力項目一覧
     */
    public function getUserFormGroups(int $userAuthorityId, array $options = [])
    {
        /** @var \App\Model\Table\FormGroupsTable $formGroupsTable */
        $formGroupsTable = $this->getTableLocator()->get('FormGroups');
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $isAdmin = $this->commonData()->existsAdminLoginData();
        $userId = Hash::get($options, 'userId');
        $excludeUserData = Hash::get($options, 'excludeUserData');

        // フォームパターン表示タイプ取得
        $patternUserAuthorityId = $userAuthorityId;
        if (((string)$userId) === '' && !$isAdmin) {
            $patternUserAuthorityId = $userAuthoritiesTable->getGuestAuthority()->get('id');
        }
        $formPatternDisplayTypes = $formPatternDisplayTypesTable->find('formCreating', [
            'inputs' => [
                'user_authority_id' => $patternUserAuthorityId,
            ],
        ])->toArray();

        $userFormGroups = [];
        foreach (
            $formGroupsTable->getFormGroups(
                FormGroup::FORM_TYPE_USER,
                ['excludeUserData' => $excludeUserData, 'cloneItem' => true]
            ) as $formGroup
        ) {
            $formGroup = clone $formGroup;

            $formItems = $formGroup->get('form_items');
            if (!is_array($formItems)) {
                $formItems = [];
            }
            foreach ($formItems as $formItemIndex => $formItem) {
                // フォームパターン表示タイプ
                $formPatternDisplayType = Hash::get($formPatternDisplayTypes, $formItem->get('id'));
                if (!isset($formPatternDisplayType)) {
                    $formPatternDisplayType = $formPatternDisplayTypesTable->createDefaultData($formItem->get('id'));
                }

                // 表示項目の設定
                $formItem->getInputTypeItem()->setConfig([
                        'userAuthorityId' => $userAuthorityId,
                    ] + $options);
                $formItem->getInputTypeItem()->settingDisplayType($formPatternDisplayType);
                if (!$formItem->getInputTypeItem()->canDisplay()) {
                    unset($formItems[$formItemIndex]);
                }
            }

            if (!empty($formItems)) {
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();

                $userFormGroups[] = $formGroup;
            }
        }

        return $userFormGroups;
    }

    /**
     * チェック情報からCSVファイルを生成
     *
     * @param array $checked チェック情報
     * @param array $condition 検索条件
     * @return callable
     */
    public function createCsvCheck(array $checked, array $condition = [])
    {
        return $this->createCsv([], 'checkUsers', [
            'checked' => $checked,
            'condition' => $condition,
        ]);
    }

    /**
     * CSVファイルを生成
     *
     * @param array $searchCondition 検索条件
     * @param string $finder ファインダー
     * @param array $options オプション
     * @return callable
     */
    public function createCsv(array $searchCondition, string $finder = 'searchList', array $options = [])
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $headerSetting = Configure::readOrFail('Setting.csv.download.user.header');
        if (isset($options['mailDelivery']) && $options['mailDelivery']) {
            $headerSetting = Configure::readOrFail('Setting.csv.download.mailDelivery.header') + $headerSetting;
        }

        $smatLock = new SmartLockLinkage();
        if (!$smatLock->useAkerun()) {
            unset($headerSetting[FormItemsTable::CSV_COLUMN_AKERUN_USER_ID]);
        }

        $csvItems = $formItemsTable->generateCsvItems('output', array_keys($headerSetting), FormGroup::FORM_TYPE_USER);
        $header = $formItemsTable->generateCsvHeader($csvItems, $headerSetting);
        $query = $this->find($finder, $options + [
            'inputs' => $searchCondition,
        ]);

        $callback = $this->getCsvStreamCallback($header, function () use (
            $csvItems,
            $searchCondition,
            $options,
            $finder
        ) {
            $page = 1;
            $lastPage = 1;
            $searchCondition['limit'] = static::CSV_PAGEVIEW;

            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            // 取得時のデータを保持するためトランザクション開始
            //一度でデータを取得するとメモリオーバーとなるため数件に分けて取得
            $connection = $this->getConnection();
            $connection->begin();
            while (true) {
                $searchCondition['page'] = $page;
                $paginator = new NumericPaginator();

                unset($query);
                $query = $paginator->paginate($this, [
                    'page' => $page,
                    'limit' => static::CSV_PAGEVIEW,
                ], [
                    'finder' => [
                        $finder => [
                            'inputs' => $searchCondition,
                            'bufferOff' => true,
                        ] + $options,
                    ],
                    'maxLimit' => static::CSV_PAGEVIEW,
                ]);

                foreach ($query as $user) {
                    yield $formItemsTable->generateCsvData($csvItems, [$this, 'formatCsvData'], [
                        'user' => $user,
                    ]);
                }

                if ($page === 1) {
                    $pagiData = $paginator->getPagingParams();
                    $lastPage = $pagiData['Users']['pageCount'];
                }

                if ($page >= $lastPage || empty($lastPage)) {
                    break;
                }

                $page++;
            }
            // rollback
            $connection->rollback();
        });

        return $callback;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param int $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatCsvData(int $column, array $options)
    {
        $user = $options['user'];

        $value = '';
        if (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_ID)) {
            $value = $user->get('id');
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_GUEST_FLG)) {
            $value = $this->csvFormat()->csvForId(
                $user->get('guest_flg'),
                Configure::readOrFail('Master.user.guestFlg.' . $user->get('guest_flg'))
            );
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_WITHDRAWAL_FLG)) {
            $value = $this->csvFormat()->csvForId(
                $user->get('withdrawal_flg'),
                Configure::readOrFail('Master.user.withdrawalFlg.' . $user->get('withdrawal_flg'))
            );
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_CREATED)) {
            $value = $this->csvFormat()->csvForTimestamp($user->get('created'));
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_MODIFIED)) {
            $value = $this->csvFormat()->csvForTimestamp($user->get('modified'));
        } elseif (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_MAIL_DELIVERY_STATUS)) {
            if ($user->has('mail_delivery_histories')) {
                foreach ($user->get('mail_delivery_histories') as $mailDeliveryHistory) {
                    $value = $this->csvFormat()->csvForId(
                        $mailDeliveryHistory->get('send_status'),
                        Configure::readOrFail(
                            'Master.mailDeliveryHistory.status.' . $mailDeliveryHistory->get('send_status')
                        )
                    );
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * サンプルCSVを生成
     *
     * @return string ファイルパス
     */
    public function createSampleCsv()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $headerSetting = Configure::readOrFail('Setting.csv.import.user.header');
        $smatLock = new SmartLockLinkage();
        if (!$smatLock->useAkerun()) {
            unset($headerSetting[FormItemsTable::CSV_COLUMN_AKERUN_USER_ID]);
        }
        $csvItems = $formItemsTable->generateCsvItems(
            'input',
            array_keys($headerSetting),
            FormGroup::FORM_TYPE_USER
        );
        $header = $formItemsTable->generateCsvHeader(
            $csvItems,
            $headerSetting
        );

        $filePath = $this->createCsvFile($header, function () use ($csvItems) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            yield $formItemsTable->generateCsvDescription($csvItems, [$this, 'formatCsvDescription']);
        });

        return $filePath;
    }

    /**
     * CSVへ出力する説明文を生成
     *
     * @param int $column カラム
     * @param array $options オプション
     * @return string 説明文
     */
    public function formatCsvDescription(int $column, array $options)
    {
        $description = '';
        if (((string)$column) === ((string)FormItemsTable::CSV_COLUMN_USER_ID)) {
            $description = Configure::readOrFail('Setting.csv.import.sample.id');
        }

        return $description;
    }

    /**
     * 認証時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAuth(Query $query, array $options)
    {
        $query->select([
            'id',
            'login_id',
            'password',
            'password_modify_timestamp',
        ])->contain(
            [
                'UserLoginHistories' => [
                    'fields' => [
                        'id',
                        'user_id',
                        'error_count',
                        'lock_timestamp',
                    ],
                ],
            ]
        );

        $query->where([
            'guest_flg' => User::GUEST_FLG_OFF,
            'withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * ログイン情報取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findLogin(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_authority_id',
            'login_id',
            'mail',
            'expiration_date_from',
            'expiration_date_to',
            'created',
            'modified',
        ]);

        $query->join([
            'UserAuthorities' => [
                'table' => 'user_authorities',
                'type' => 'INNER',
                'conditions' => [
                    'Users.user_authority_id = UserAuthorities.id',
                ],
            ],
        ]);

        $query->contain([
            'UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                    'access',
                    'form_pattern_id',
                    'calendar_type',
                    'calendar_type_default',
                    'login_name_form_item_id',
                    'reservation_limit_all',
                    'reservation_limit_future',
                    'reservation_limit_month',
                    'reservation_limit_day',
                    'charge_multiplier',
                ],
            ],
        ]);

        $query->formatResults(function ($results) {
            $result = $results->map(function ($user) {
                $userAdditionTable = $this->getTableLocator()->get('UserAdditions');

                $formItemId = $user->get('user_authority')->get('login_name_form_item_id');
                if (isset($formItemId)) {
                    $userAdditions = $userAdditionTable->find('loginName', [
                        'inputs' => [
                            'user_id' => $user->get('id'),
                            'form_item_id' => $formItemId,
                        ],
                    ]);
                    $user->set('user_additions', $userAdditions->toArray());
                    $user->clean();
                }

                return $user;
            });

            return $result;
        });

        return $query;
    }

    /**
     * リマインダーのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReminder(Query $query, array $options)
    {
        $query->select([
            'id',
            'mail',
            'user_authority_id',
            'login_id',
            'expiration_date_from',
            'expiration_date_to',
            'created',
            'modified',
            'Users__addition_values' => $this->driverExpression()->createJson([
                'form_item_id' => 'form_item_id',
                'value' => 'value',
            ]),
        ])->contain([
            'UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
            'UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        $query->where([
            'Users.mail' => $options['mail'],
            'Users.guest_flg' => User::GUEST_FLG_OFF,
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ])->join([
            'table' => 'user_additions',
            'alias' => 'UserAdditions',
            'type' => 'LEFT',
            'conditions' => 'Users.id = UserAdditions.user_id',
        ]);

        $query->group([
            'Users.id',
            'UserAuthorities.id',
            'UserSmartLocks.id',
        ]);

        return $query;
    }

    /**
     * ログイン情報取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReminderUser(Query $query, array $options)
    {
        $query->select([
            'id',
        ])->where([
            'id' => Hash::get($options, 'userId'),
            'login_id' => Hash::get($options, 'loginId'),
        ]);

        return $query;
    }

    /**
     * パスワード取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findPassword(Query $query, array $options)
    {
        $query->select([
            'id',
            'password',
        ])->where([
            'id' => Hash::get($options, 'userId'),
            'guest_flg' => User::GUEST_FLG_OFF,
            'withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * パスワード更新
     *
     * @param \Cake\Datasource\EntityInterface $entity 会員情報
     * @param string $newPassword パスワード
     * @return bool
     */
    public function updatePassword($entity, $newPassword)
    {
        $entity->clean();
        $entity->set('password', $newPassword);

        $result = $this->getConnection()->transactional(function () use ($entity) {
            $result = $this->save($entity, ['mailSendFlg' => false]);

            if (!$result) {
                return false;
            }

            $reminderTokenTable = $this->getTableLocator()->get('UserPasswordReminderTokens');
            $reminderTokenTable->deleteAll(['user_id' => $entity->get('id')]);

            return $result;
        });

        return $result;
    }

    /**
     * プランの上限をチェック
     *
     * @return bool
     */
    public function checkPlanRestriction()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $contractPlan = $systemSettingsTable->getData()->get('contract_plan');
        $limit = Configure::read('Master.systemSetting.restriction.user.' . $contractPlan);
        if (!isset($limit)) {
            return true;
        }

        if ($this->find('planRestriction')->count() >= $limit) {
            return false;
        }

        return true;
    }

    /**
     * ログインIDのロックを取得
     *
     * @param string $loginId ログインID
     * @return int
     */
    protected function getLockForLoginId($loginId)
    {
        $lockCode = $this->generateLockCode($loginId);

        $this->getLock(static::LOCK_TYPE_UNIQUE_LOGIN_ID, $lockCode);

        return $lockCode;
    }

    /**
     * ログインIDのロックを解放
     *
     * @param int $lockCode ロックコード
     * @return void
     */
    protected function releaseLockForLoginId($lockCode)
    {
        $this->releaseLock(static::LOCK_TYPE_UNIQUE_LOGIN_ID, $lockCode);
    }

    /**
     * メールアドレスのロックを取得
     *
     * @param string $mail メールアドレス
     * @return int
     */
    protected function getLockForMail($mail)
    {
        $lockCode = $this->generateLockCode($mail);

        $this->getLock(static::LOCK_TYPE_UNIQUE_MAIL, $lockCode);

        return $lockCode;
    }

    /**
     * メールアドレスのロックを解放
     *
     * @param int $lockCode ロックコード
     * @return void
     */
    protected function releaseLockForMail($lockCode)
    {
        $this->releaseLock(static::LOCK_TYPE_UNIQUE_MAIL, $lockCode);
    }

    /**
     * @inheritDoc
     */
    public function tryLockForImport()
    {
        return $this->tryLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_USER);
    }

    /**
     * @inheritDoc
     */
    public function getLockForImport()
    {
        $this->getLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_USER);
    }

    /**
     * @inheritDoc
     */
    public function releaseLockForImport()
    {
        $this->releaseLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_USER);
    }

    /**
     * スマートロック連携時のファインダー
     *
     * guest_flg 関係なく取得する
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSmartLock(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_authority_id',
            'login_id',
            'mail',
            'guest_flg',
            'withdrawal_flg',
        ]);

        $query->contain([
            'UserAdditions',
            'UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * スマートロック連携
     *
     * @param \Cake\Datasource\EntityInterface $user 会員
     * @param bool $isNew 登録か
     * @param \Cake\Datasource\EntityInterface $originalUser save前の会員entity
     * @return void
     * @throws \Exception
     */
    protected function smartLockLinkage(
        EntityInterface $user,
        bool $isNew,
        ?EntityInterface $originalUser = null
    ): void {
        $smartLockLinkage = new SmartLockLinkage();
        if (!$smartLockLinkage->useSmartLock()) {
            return;
        }

        $user->set('original_user', $originalUser);
        /** @var \App\Model\Entity\User $user */
        $smartLockLinkage->addUser($user);
        $user->set('original_user', null);

        // 自動返信メールの情報は beforeSave でセット済みなので、AkerunユーザーIDの置き換え文言用にスマートロック情報を追加して再登録
        /** @var \App\Model\Table\UserSmartLocksTable $userSmartLocksTable */
        $userSmartLocksTable = $this->getTableLocator()->get('UserSmartLocks');
        $userSmartLock = $userSmartLocksTable
            ->find('userAddAutoReplyMail', [
                'user_id' => $user->id,
            ])->first();
        if (!($userSmartLock instanceof UserSmartLock)) {
            return;
        }
        $autoReplyMailHistories = $user->get('auto_reply_mail_histories');
        if (!is_array($autoReplyMailHistories)) {
            return;
        }

        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        $autoReplyMailHistories = Hash::map(
            $autoReplyMailHistories,
            '{*}',
            function ($autoReplyMailHistory) use ($autoReplyMailHistoriesTable, $userSmartLock) {
                if (
                    !$autoReplyMailHistory instanceof AutoReplyMailHistory
                    || $autoReplyMailHistory->get('auto_reply_mail_id') !== AutoReplyMail::TYPE_USER_ADD
                ) {
                    // 会員登録メール以外は何もしない
                    return $autoReplyMailHistory;
                }

                $data = Hash::insert(
                    (array)($autoReplyMailHistory->get('data')),
                    'user.user_smart_lock.smart_lock_user_id',
                    $userSmartLock->get('smart_lock_user_id')
                );
                $autoReplyMailHistory->set('data', $data);
                $autoReplyMailHistoriesTable->save($autoReplyMailHistory);

                return $autoReplyMailHistory;
            }
        );
        $user->set('auto_reply_mail_histories', $autoReplyMailHistories);
    }

    /**
     * メールアドレス更新
     *
     * @param \Cake\Datasource\EntityInterface $entity 会員情報
     * @param \App\Model\Entity\OptinToken $optinToken オプトイン
     * @return bool
     */
    public function updateMail($entity, $optinToken)
    {
        $entity->clean();
        $entity->set('mail', $optinToken->get('mail'));

        if (!$this->save($entity, ['mailSendFlg' => false, 'optinToken' => $optinToken,])) {
            return false;
        }

        return true;
    }

    /**
     * 権限を更新し、処理が正常に行われたか判定
     *
     * @param \Cake\Datasource\EntityInterface $user 会員
     * @param int $userAuthorityId 権限
     * @param array|null $saveOperation 操作ログ
     * @return \App\Model\Entity\User|false
     */
    public function isUpdateAuthority(EntityInterface $user, int $userAuthorityId, ?array $saveOperation = null)
    {
        $user->clean();
        $user->set('user_authority_id', $userAuthorityId);

        return $this->save($user, $saveOperation);
    }

    /**
     * 承認によってユーザーの権限を更新
     *
     * @param \App\Model\Entity\User $user 会員
     * @param array $saveOptions 操作ログ
     * @return \App\Model\Entity\User|false
     */
    public function updateAuthorityForApproval($user, $saveOptions = null)
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');
        /** @var \App\Model\Table\FormItemChoicesTable $formItemChoicesTable */
        $formItemChoicesTable = $this->fetchTable('FormItemChoices');

        $attributeId = null;
        $userAuthority = null;

        // 顧客に登録されている属性を取得
        $userAdditions = $user->get('user_additions');
        foreach ($userAdditions as $userAddition) {
            if ($userAddition->form_item_id === formItem::FORM_ITEMS_ID_ATTRIBUTE) {
                $attributeId = $userAddition->value;
                break;
            }
        }
        if ($attributeId !== null) {
            $attribute = $formItemChoicesTable->get($attributeId);
            $attributeName = $attribute->name;

            // 顧客に登録されている属性と同じ名前の権限名の顧客の権限データを取得
            $userAuthority = $userAuthoritiesTable->getSameNameAuthority($attributeName);
        }

        if ($userAuthority === null) {
            throw new BadRequestException(Message::NO_EXIST_ATTRIBUTE_AUTHORITY_NAME);
        }

        // 取得した権限を会員の権限に書き換える
        return $this->isUpdateAuthority($user, (int)$userAuthority['id'], $saveOptions);
    }
}
