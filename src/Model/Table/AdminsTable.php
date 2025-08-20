<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Admin;
use App\Model\Entity\AdminAuthority;
use App\Utility\ArrayUtility;
use App\Utility\StringUtility;
use App\Validation\PasswordValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Admins Model
 *
 * @method \App\Model\Entity\Admin newEmptyEntity()
 * @method \App\Model\Entity\Admin newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Admin[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Admin get($primaryKey, $options = [])
 * @method \App\Model\Entity\Admin findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Admin patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Admin[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Admin|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Admin saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Admin[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Admin[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Admin[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Admin[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminsTable extends AppTable
{
    public const INITIAL_PASSWORD_LENGTH = 8;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
        ]);
        $this->belongsTo('AdminAuthorities', [
            'foreignKey' => 'admin_authority_id',
        ]);
        $this->hasOne('AdminLoginHistories', [
            'foreignKey' => 'admin_id',
            'dependent' => true,
        ]);
        $this->hasMany('AdminListItems', [
            'foreignKey' => 'admin_id',
            'dependent' => true,
        ]);
        $this->hasMany('AdminMails', [
            'foreignKey' => 'admin_id',
            'dependent' => true,
            'saveStrategy' => 'replace',
        ]);
        $this->hasMany('AdminOperationalLogs', [
            'foreignKey' => 'admin_id',
        ]);
        $this->hasMany('AdminPassResetTokens', [
            'foreignKey' => 'admin_id',
            'dependent' => true,
        ]);
        $this->hasMany('AdminSearchItems', [
            'foreignKey' => 'admin_id',
            'dependent' => true,
        ]);
        $this->hasMany('AutoReplyMailHistories', [
            'foreignKey' => 'admin_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
        $this->getBehavior('AccountLock')->setConfig('admin', true);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('authority', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('authority', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('authority', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('authority'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $loginIdLength = Configure::readOrFail('Setting.auth.admin.loginId.length');
        $validator
            ->requirePresence('login_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('login_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('login_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'minLength' => [
                    'rule' => ['minLength', $loginIdLength['min']],
                    'last' => true,
                    'message' => __(Message::ERROR_MIN_LENGTH, $loginIdLength['min']),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', $loginIdLength['max']],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, $loginIdLength['max']),
                ],
                'custom' => [
                    'rule' => ['custom', Configure::readOrFail('Setting.auth.admin.loginId.character')],
                    'last' => true,
                    'message' => __(Message::ERROR_LOGIN_ID_CHARACTER),
                ],
            ]);

        $validator
            ->requirePresence('label_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator = PasswordValidation::getAdminValidator($validator);
        $validator->add('password', [
            'samePassword' => [
                'rule' => function ($value, $context) {
                    return $this->checkSamePassword($context['data']['id'], $value);
                },
                'on' => 'update',
                'last' => true,
                'message' => __(Message::ERROR_PASSWORD_SAME),
            ],
        ]);

        $validator
            ->requirePresence('admin_mails', true, __(Message::ERROR_NOT_EMPTY))
            // 初期管理者フラグがONの場合必須
            ->allowEmptyArray('admin_mails', __(Message::ERROR_NOT_EMPTY), function ($context) {
                // 新規登録時は必須ではない
                if (!Hash::get($context['data'], 'id', false)) {
                    return true;
                }
                try {
                    /** @var \App\Model\Table\AdminsTable $adminTable */
                    $adminTable = $this->getTableLocator()->get('Admins');
                    $admin = $adminTable->get($context['data']['id'], [
                        'finder' => 'auth',
                    ]);
                    if ($admin->initial_admin_flg === Admin::INITIAL_ADMIN_FLG_ON) {
                        return false;
                    }
                } catch (RecordNotFoundException $e) {
                    return true;
                }

                return true;
            })
            ->add('admin_mails', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        if ($this->canAdminAuthorities()) {
            $validator
                ->requirePresence('admin_authority_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyArray('admin_authority_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                ->add('admin_authority_id', [
                    'multiple' => [
                        'rule' => [
                            'multiple',
                            [
                                'in' => array_keys($this->getFieldValueOptions('adminAuthorityList')),
                            ],
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * 前回と同じパスワードはエラー
     *
     * @param int $id 管理者ID
     * @param string $value パスワード
     * @return bool
     */
    public function checkSamePassword($id, $value)
    {
        try {
            $admin = $this->get($id, [
                'finder' => 'auth',
            ]);

            if ($admin->passwordCheck($value)) {
                return false;
            }
        } catch (RecordNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['login_id'], __(Message::ERROR_EXISTS)), 'loginIdIsUnique');

        return $rules;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('authority', [
                'multiValue' => true,
            ])
            ->like('login_id', [
                'before' => true,
                'after' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->getTableLocator()->get('AdminAuthorities');

        $fieldValueOptions = [
            'authority' => Configure::readOrFail('Master.admin.authority'),
            'adminAuthorityList' => $adminAuthoritiesTable->adjustList(),
        ];

        return $fieldValueOptions;
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
            'password_reset_flg',
            'initial_admin_flg',
        ])->contain(
            [
                'AdminLoginHistories' => [
                    'fields' => [
                        'id',
                        'admin_id',
                        'error_count',
                        'lock_timestamp',
                    ],
                ],
            ]
        );

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
            'label_id',
            'authority',
            'login_id',
            'password_modify_timestamp',
            'password_reset_flg',
            'initial_admin_flg',
            'system_admin_flg',
            'created',
            'modified',
        ]);
        $query->contain([
            'AdminAuthorities' => [
                'fields' => [
                    'access_setting',
                    'access_operator',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * API用のログイン情報取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findApiLogin(Query $query, array $options)
    {
        $query = $this->callFinder('login', $query, $options);
        $query->select(['password']);
        $query->where([
            'Admins.login_id' => $options['inputs']['login_id'],
        ]);
        $query->limit(1);

        $query->formatResults(function ($results) use ($options) {
            $results = $results->map(function ($admin) use ($options) {
                if ($admin instanceof Admin && !$admin->passwordCheck((string)$options['inputs']['password'])) {
                    return null;
                }

                return $admin;
            });

            return $results;
        });

        return $query;
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
        $query->contain([
            'Labels' => [
                'fields' => [
                    'name',
                ],
            ],
            'AdminMails' => [
                'fields' => [
                    'id',
                    'admin_id',
                    'mail',
                ],
            ],
        ]);

        return $query;
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
            'initial_admin_flg',
            'system_admin_flg',
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->select([
            'id',
            'authority',
            'login_id',
            'initial_admin_flg',
            'system_admin_flg',
            'admin_authority_id',
        ]);
        $query->contain([
            'AdminAuthorities' => [
                'fields' => [
                    'name',
                ],
            ],
            'Labels' => [
                'fields' => [
                    'name',
                ],
            ],
        ]);

        $query->join([
            'AdminAuthorities' => [
                'table' => 'admin_authorities',
                'type' => 'LEFT',
                'conditions' => 'AdminAuthorities.id = Admins.admin_authority_id',
            ],
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Admins.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if (!$loginData->isMasterAdmin()) {
                $query->where([
                    'Admins.id' => $loginData->get('id'),
                ]);
            }
            if (!$loginData->isSystemAdmin()) {
                $query->where([
                    'Admins.system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF,
                ]);
            }
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query->order([
                'Admins.' . $sort => $direction,
            ] + [
                'Admins.label_id' => $direction,
                'Admins.authority' => $direction,
                'Admins.login_id' => $direction,
                'Admins.id' => $direction,
            ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * ログインIDからIDとメールアドレス（1番目）を取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMail(Query $query, array $options)
    {
        $query->select(['id', 'login_id'])->contain([
            'AdminMails' => [
                'fields' => [
                    'id',
                    'admin_id',
                    'mail',
                ],
                'sort' =>
                    [
                        'AdminMails.id' => 'ASC',
                    ],
            ]]);

        $query->where([
            'login_id' => $options['login_id'],
            'initial_admin_flg' => Admin::INITIAL_ADMIN_FLG_ON,
            'system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * パスワード変更用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findPasswordChange(Query $query, array $options)
    {
        $query->select([
            'id',
            'label_id',
            'authority',
            'login_id',
            'password_modify_timestamp',
            'password_reset_flg',
            'initial_admin_flg',
            'system_admin_flg',
            'password',
            'initial_password',
        ]);
        $query->where([
            'Admins.login_id' => $options['inputs']['login_id'],
        ]);

        return $query;
    }

    /**
     * 初期管理者取得用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findInitialAdmin(Query $query, array $options)
    {
        $query->select([
            'id',
        ]);
        $query->where([
            'Admins.authority' => Admin::AUTHORITY_MASTER,
            'Admins.initial_admin_flg' => Admin::INITIAL_ADMIN_FLG_ON,
            'Admins.system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF,
        ]);
        $query->order([
            'Admins.id' => 'ASC',
        ]);
        $query->limit(1);

        return $query;
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
        if (!isset($data['admin_mails']) || !is_array($data['admin_mails'])) {
            $data->offsetSet('admin_mails', []);
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
        if (!($entity instanceof Admin)) {
            throw new CakeException();
        }

        if ($entity->isNew()) {
            /** @var \App\Model\Table\AdminListItemsTable $adminListItemsTable */
            $adminListItemsTable = $this->getAssociation('AdminListItems')->getTarget();
            /** @var \App\Model\Table\AdminSearchItemsTable $adminSearchItemsTable */
            $adminSearchItemsTable = $this->getAssociation('AdminSearchItems')->getTarget();

            $defaults = [
                'password_reset_flg' => Admin::PASSWORD_RESET_FLG_OFF,
                'initial_admin_flg' => Admin::INITIAL_ADMIN_FLG_OFF,
                'system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF,
                'admin_list_items' => $adminListItemsTable->createDefaultData(),
                'admin_search_items' => $adminSearchItemsTable->createDefaultData(),
            ];
            if ($entity->isInitialAdmin() || $entity->isSystemAdmin()) {
                $defaults['authority'] = Admin::AUTHORITY_MASTER;
            }
            if ($entity->isSystemAdmin()) {
                $defaults['password_reset_flg'] = Admin::PASSWORD_RESET_FLG_ON;
                $defaults['initial_admin_flg'] = Admin::INITIAL_ADMIN_FLG_ON;
            }
            foreach ($defaults as $key => $value) {
                if ((string)$entity->get($key) === '') {
                    $entity->set($key, $value);
                }
            }
        }

        //初期管理者はマスター固定
        if ($entity->isInitialAdmin()) {
            $entity->set('authority', Admin::AUTHORITY_MASTER);
        }

        if ((string)$entity->get('password') === '') {
            $entity->setDirty('password', false);
        }

        if ($entity->isDirty('password') && !$entity->isNew()) {
            $entity->set('password_reset_flg', Admin::PASSWORD_RESET_FLG_ON);
            $entity->set('password_modify_timestamp', clone $this->commonData()->getNowDateTime());
        }
    }

    /**
     * パスワード初期化
     *
     * @param int $id 管理者ID
     * @param string $password パスワード
     * @return bool
     */
    public function resetPassword($id, $password)
    {
        $query = $this->updateQuery()->update()
            ->set([
                'password' => $password,
                'password_modify_timestamp' => $this->commonData()->getNowDateTime(),
                'password_reset_flg' => Admin::PASSWORD_RESET_FLG_OFF,
                'modified' => $this->commonData()->getNowDateTime(),
            ])
            ->where([
                'id' => $id,
            ]);

        $result = $query->execute();
        if ($result->rowCount() < 1) {
            return false;
        }

        return true;
    }

    /**
     * 管理者の登録可能チェック
     *
     * @return bool 判定結果
     */
    public function canAddAdmin()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();
            if ($loginData->isSystemAdmin() || !$loginData->isMasterAdmin()) {
                return false;
            }

            /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
            $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

            $systemSetting = $systemSettingsTable->getData();
            $canAddAdminContractPlan = Configure::readOrFail('Master.systemSetting.canAddAdminContractPlan');
            if (!ArrayUtility::inArray($systemSetting->get('contract_plan'), $canAddAdminContractPlan)) {
                return false;
            }
        }

        return true;
    }

    /**
     * システム管理者を作成
     *
     * @param string|null $loginId ログインID
     * @param string|null $hashedPassword ハッシュ化パスワード
     * @return \App\Model\Entity\Admin
     */
    public function createSystemAdmin(?string $loginId = null, ?string $hashedPassword = null)
    {
        if (((string)$loginId) === '') {
            $loginId = Configure::readOrFail('Env.systemAdmin.loginId');
        }

        $admin = $this->newEntity([
            'login_id' => $loginId,
        ], ['validate' => false]);
        $admin->set('system_admin_flg', Admin::SYSTEM_ADMIN_FLG_ON);

        if (((string)$hashedPassword) === '') {
            $hashedPassword = Configure::read('Env.systemAdmin.hashedPassword');
        }
        if (((string)$hashedPassword) !== '') {
            $admin->set('password', $hashedPassword, ['setter' => false]);
        } else {
            $admin->set('raw_password', $this->generateInitialPassword());
            $admin->set('password', $admin->get('raw_password'));
        }
        $admin->set('initial_password', $admin->get('password'));
        $admin->set('admin_authority_id', AdminAuthority::DEFAULT_ID);

        return $admin;
    }

    /**
     * 管理者を作成
     *
     * @param string $loginId ログインID
     * @param string|null $hashedPassword ハッシュ化パスワード
     * @param int $authority 権限
     * @param bool $initial 初期管理者フラグ
     * @param bool $passwordReset パスワード初期化フラグ
     * @return \App\Model\Entity\Admin
     */
    public function createAdmin(string $loginId, $hashedPassword, int $authority, $initial, $passwordReset)
    {
        $admin = $this->newEntity([
            'login_id' => $loginId,
        ], ['validate' => false]);

        if (((string)$hashedPassword) !== '') {
            $admin->set('password', $hashedPassword, ['setter' => false]);
        } else {
            $admin->set('raw_password', $this->generateInitialPassword());
            $admin->set('password', $admin->get('raw_password'));
        }

        if ($initial) {
            $admin->set('initial_admin_flg', Admin::INITIAL_ADMIN_FLG_ON);
            $admin->set('initial_password', $admin->get('password'));
        }
        if ($passwordReset) {
            $admin->set('password_reset_flg', Admin::PASSWORD_RESET_FLG_ON);
        }

        $admin->set('authority', $authority);
        $admin->set('admin_authority_id', AdminAuthority::DEFAULT_ID);

        return $admin;
    }

    /**
     * 初期パスワードを生成
     *
     * @return string
     */
    public function generateInitialPassword()
    {
        $password = StringUtility::randomString(static::INITIAL_PASSWORD_LENGTH);

        return $password;
    }

    /**
     * 利用許可画面のパターン設定可能チェック
     *
     * @return bool 判定結果
     */
    public function canAdminAuthorities()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if ($loginData->isMasterAdmin()) {
                return true;
            }
        }

        return false;
    }

    /**
     * カテゴリ設定済み管理者チェック（マスター管理者を除く）
     *
     * @return bool 判定結果
     */
    public function checkLabelIdAdmin()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            /** @var \App\Model\Entity\Admin $loginData */
            $loginData = $this->commonData()->getAdminLoginData();

            if (!$loginData->isMasterAdmin() && $this->commonData()->getAdminLoginLabel()) {
                return true;
            }
        }

        return false;
    }

    /**
     * 件数取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCountAuthorityId(Query $query, array $options)
    {
        $query->select(['Admins.id']);

        $adminAuthorityId = Hash::get($options, 'inputs.admin_authority_id');
        $query->where([
            'Admins.admin_authority_id IN' => (array)$adminAuthorityId,
        ]);

        return $query;
    }
}
