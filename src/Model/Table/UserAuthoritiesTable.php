<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\FormGroup;
use App\Model\Entity\UserAuthority;
use App\Model\InputType\Item\Type\LoginNameInterface;
use App\Utility\ArrayUtility;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validation as Validation;

/**
 * UserAuthorities Model
 *
 * @method \App\Model\Entity\UserAuthority newEmptyEntity()
 * @method \App\Model\Entity\UserAuthority newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserAuthority[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserAuthority get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserAuthority findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserAuthority patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserAuthority[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserAuthority|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserAuthority saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserAuthority[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAuthority[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAuthority[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserAuthority[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UserAuthoritiesTable extends AppTable
{
    public const NAME_MAX = 100;
    public const RESERVATION_LIMIT_MAX = 1000000;
    public const CHARGE_MULTIPLIER_MAX = 3;

    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected $guestAuthority = null;

    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected $defaultAuthority = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormPatterns', [
            'foreignKey' => 'form_pattern_id',
            'joinType' => 'INNER',
        ])->setConditions(['form_type' => FormGroup::FORM_TYPE_USER]);

        $this->belongsTo('FormItems', [
            'foreignKey' => 'login_name_form_item_id',
        ]);
        $this->hasMany('AutoReplyMails', [
            'foreignKey' => 'user_authority_id',
        ]);
        $this->hasMany('NewsAuthorities', [
            'foreignKey' => 'user_authority_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'user_authority_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema
            ->setColumnType('access', 'json')
            ->setColumnType('calendar_type', 'json');

        return $schema;
    }

    /**
     * beforeSave
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $accesses = $entity->get('access');

        if (
            is_array($accesses)
            && ArrayUtility::arraySearch(Configure::readOrFail('Master.userAuthority.frontCode.All'), $accesses)
            !== false
        ) {
            $entity->set('access', [Configure::readOrFail('Master.userAuthority.frontCode.All')]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $authorityPage = Configure::readOrFail('Master.userAuthority.frontValue');

        $frontPageList = [];
        foreach ($authorityPage as $actions) {
            $frontPageList += $actions;
        }

        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItems = [];
        foreach ($formItemsTable->getFormItems(FormGroup::FORM_TYPE_USER) as $formItem) {
            if ($formItem->getInputTypeItem() instanceof LoginNameInterface) {
                $formItems[$formItem->get('id')] = $formItem->get('name');
            }
        }

        /** @var \App\Model\Table\FormPatternsTable $formPatternsTable */
        $formPatternsTable = $this->getAssociation('FormPatterns')->getTarget();
        $userFormPatternList = $formPatternsTable->getFormPatternList(FormGroup::FORM_TYPE_USER);

        $fieldValueOptions = [
            'frontPageControllers' => Configure::readOrFail('Master.userAuthority.front'),
            'frontPageValue' => $authorityPage,
            'frontPageActions' => $frontPageList,
            'calendarType' => Configure::readOrFail('Master.event.calendarType'),
            'loginNameFormItemId' => $formItems,
            'userFormPatterns' => $userFormPatternList,
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('access', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('access')
            ->add('access', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('frontPageActions')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('calendar_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('calendar_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('calendar_type', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('calendarType')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('calendar_type_default', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('calendar_type_default', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('calendar_type_default', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('calendarType')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('login_name_form_item_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('login_name_form_item_id')
            ->add('login_name_form_item_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('loginNameFormItemId')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('form_pattern_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('form_pattern_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('form_pattern_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('userFormPatterns')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $addRule['reservation_limit'] = [
            'isScalar' => [
                'rule' => ['isScalar'],
                'last' => true,
                'message' => __(Message::ERROR_INVALID_VALUE),
            ],
            'naturalNumber' => [
                'rule' => ['naturalNumber', true],
                'last' => true,
                'message' => __(Message::ERROR_NUMBER),
            ],
            'lessThanOrEqualDay' => [
                'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::RESERVATION_LIMIT_MAX],
                'last' => true,
                'message' => __(Message::ERROR_OVER_DIGIT, static::RESERVATION_LIMIT_MAX),
            ],
        ];

        $validator
            ->requirePresence('reservation_limit_future', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_future')
            ->add('reservation_limit_future', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_month', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_month')
            ->add('reservation_limit_month', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_day', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_day')
            ->add('reservation_limit_day', $addRule['reservation_limit']);

        $validator
            ->requirePresence('reservation_limit_all', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_limit_all')
            ->add('reservation_limit_all', $addRule['reservation_limit']);

        $validator
            ->requirePresence('charge_multiplier', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('charge_multiplier')
            ->add('charge_multiplier', [
                'custom' => [
                    'rule' => ['custom', '/^\d+(\.\d+)?$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_HALF_SIZE_DECIMAL_NUMBER),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CHARGE_MULTIPLIER_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CHARGE_MULTIPLIER_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * 権限の一覧を取得（id:name）
     *
     * @param bool $includeAll 全ての選択肢尾を含む
     * @param bool $includeGuest ゲストの選択肢尾を含む
     * @return array
     */
    public function getSelectList($includeAll = true, $includeGuest = true)
    {
        $query = $this->find('selectList');
        if (!$includeGuest) {
            $query->where([
                'UserAuthorities.guest_flg' => UserAuthority::GUEST_FLG_OFF,
            ]);
        }

        $userAuthorityLists = $query->toArray();
        if ($includeAll) {
            $userAuthorityLists = Configure::readOrFail('Master.userAuthority.selectAll') + $userAuthorityLists;
        }

        return $userAuthorityLists;
    }

    /**
     * ゲスト権限を取得
     *
     * @return \Cake\Datasource\EntityInterface
     */
    public function getGuestAuthority()
    {
        if (!isset($this->guestAuthority)) {
            $userAuthority = $this->find('guestAuthority')->first();
            if (!($userAuthority instanceof EntityInterface)) {
                throw new CakeException();
            }
            $this->guestAuthority = $userAuthority;
        }

        return $this->guestAuthority;
    }

    /**
     * デフォルト権限を取得
     *
     * @return \Cake\Datasource\EntityInterface
     */
    public function getDefaultAuthority()
    {
        if (!isset($this->defaultAuthority)) {
            $userAuthority = $this->find('defaultAuthority')->first();
            if (!($userAuthority instanceof EntityInterface)) {
                throw new CakeException();
            }
            $this->defaultAuthority = $userAuthority;
        }

        return $this->defaultAuthority;
    }

    /**
     * 選択肢のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSelectList(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
        ]);
        $query->order([
            'id' => 'ASC',
        ]);

        return $this->callFinder('list', $query, [
            'keyField' => 'id',
            'valueField' => 'name',
        ]);
    }

    /**
     * ゲスト権限のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findGuestAuthority(Query $query, array $options)
    {
        $query->select([
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
        ]);
        $query->where([
            'UserAuthorities.guest_flg' => UserAuthority::GUEST_FLG_ON,
            'UserAuthorities.default_flg' => UserAuthority::DEFAULT_FLG_ON,
        ]);

        return $query;
    }

    /**
     * デフォルト権限のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefaultAuthority(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'access',
            'form_pattern_id',
            'calendar_type',
            'calendar_type_default',
            'reservation_limit_all',
            'reservation_limit_future',
            'reservation_limit_month',
            'reservation_limit_day',
            'charge_multiplier',
        ]);
        $query->where([
            'UserAuthorities.guest_flg' => UserAuthority::GUEST_FLG_OFF,
            'UserAuthorities.default_flg' => UserAuthority::DEFAULT_FLG_ON,
        ]);

        return $query;
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
        $query->select([
            'UserAuthorities.id',
            'UserAuthorities.name',
            'UserAuthorities.access',
            'UserAuthorities.calendar_type_default',
            'UserAuthorities.login_name_form_item_id',
            'UserAuthorities.calendar_type',
            'UserAuthorities.form_pattern_id',
            'UserAuthorities.default_flg',
            'UserAuthorities.guest_flg',
        ]);

        if (isset($options['usersCount']) && $options['usersCount'] === true) {
            $query->contain([
                'Users' => function ($q) {
                    $q->select([
                        'userTotal' => $q->func()->count('Users.user_authority_id'),
                        'user_authority_id',
                    ])->group(['Users.user_authority_id'])
                        ->order(['user_authority_id' => 'desc'], true);

                    return $q;
                },
            ]);
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'UserAuthorities.' . $sort => $direction,
                ] + [
                    'UserAuthorities.id' => $direction,
                ], true);

        return $query;
    }

    /**
     * 編集のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
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
        ]);

        return $query;
    }

    /**
     * 削除用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select([
            'id',
            'guest_flg',
            'default_flg',
        ])->contain(['Users' => function (Query $q) {
            $q->select([
                'user_authority_id',
                'count' => $q->func()->count('user_authority_id'),
            ])
                ->group(['user_authority_id'])
                ->order('user_authority_id', true);

            return $q;
        }]);

        $query->where([
            'default_flg' => UserAuthority::DEFAULT_FLG_OFF,
            'guest_flg' => UserAuthority::GUEST_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * 回数制限チェック時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservationLimit(Query $query, array $options)
    {
        $query->select([
            'id',
            'reservation_limit_all',
            'reservation_limit_future',
            'reservation_limit_month',
            'reservation_limit_day',
        ]);

        return $query;
    }

    /**
     * 属性と同じ名前の権限名のデータを取得
     *
     * @param string $name 属性名
     * @return \App\Model\Entity\UserAuthority|null
     */
    public function getSameNameAuthority($name)
    {
        $userAuthority = $this->userAuthorities->find()
            ->where(['name' => $name])
            ->first();

        return $userAuthority;
    }
}
