<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AdminAuthority;
use App\Utility\ArrayUtility;
use App\Utility\SmartLock\SmartLockLinkage;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * AdminAuthorities Model
 *
 * @method \App\Model\Entity\AdminAuthority newEmptyEntity()
 * @method \App\Model\Entity\AdminAuthority newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminAuthority[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminAuthority get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminAuthority findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminAuthority patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminAuthority[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminAuthority|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminAuthority saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminAuthority[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminAuthority[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminAuthority[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminAuthority[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminAuthoritiesTable extends AppTable
{
    public const NAME_MAX = 100;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

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
            ->setColumnType('access_setting', 'json')
            ->setColumnType('access_operator', 'json');

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
        $all = Configure::readOrFail('Master.adminAuthority.frontCode.All');

        $accessSettings = $entity->get('access_setting');
        if (
            is_array($accessSettings)
            && ArrayUtility::arraySearch($all, $accessSettings)
            !== false
        ) {
            $entity->set('access_setting', [$all]);
        }

        $accessOperators = $entity->get('access_operator');
        if (
            is_array($accessOperators)
            && ArrayUtility::arraySearch($all, $accessOperators)
            !== false
        ) {
            $entity->set('access_operator', [$all]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $accessSetting = Configure::readOrFail('Master.adminAuthority.accessSetting');
        // ビデオ会議設定を利用しない場合
        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            foreach (Configure::readOrFail('Master.adminAuthority.useVideoMeeting') as $value) {
                unset($accessSetting[$value]);
            }
        }
        // 決済機能を利用しない場合
        if (!$systemSettingsTable->getData()->usePayment()) {
            foreach (Configure::readOrFail('Master.adminAuthority.usePayment') as $value) {
                unset($accessSetting[$value]);
            }
        }
        // スマートロック設定（アケルン）を利用しない場合
        $smartLock = new SmartLockLinkage();
        if (!$smartLock->useAkerun()) {
            foreach (Configure::readOrFail('Master.adminAuthority.useSmartLocks') as $value) {
                unset($accessSetting[$value]);
            }
        }
        $fieldValueOptions = [
            'accessSettingList' => $accessSetting,
            'accessOperatorList' => Configure::readOrFail('Master.adminAuthority.accessOperator'),
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
            ->requirePresence('access_setting', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('access_setting')
            ->add('access_setting', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('accessSettingList')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('access_operator', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('access_operator')
            ->add('access_operator', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('accessOperatorList')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
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
            'AdminAuthorities.id',
            'AdminAuthorities.name',
            'AdminAuthorities.access_setting',
            'AdminAuthorities.access_operator',
            'AdminAuthorities.default_flg',
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'AdminAuthorities.' . $sort => $direction,
                ] + [
                    'AdminAuthorities.id' => $direction,
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
            'access_setting',
            'access_operator',
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
            'default_flg',
        ]);

        $query->where([
            'default_flg' => AdminAuthority::DEFAULT_FLG_OFF,
        ]);

        return $query;
    }

    /**
     * 管理者情報用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAuthorityList(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
        ]);

        return $query;
    }

    /**
     * 管理者情報用データ成型
     *
     * @return array
     */
    public function adjustList()
    {
        $data = $this->find('authorityList');

        $adminAuthorityList = [];
        foreach ($data as $values) {
            $adminAuthorityList[$values['id']] = $values['name'];
        }

        return $adminAuthorityList;
    }

    /**
     * 権限データ成型
     *
     * @param \Cake\Datasource\EntityInterface|null $adminAuthoritiesData 権限データ
     * @return array
     */
    public function getAccessData($adminAuthoritiesData)
    {
        $accessData = [];
        $all = Configure::readOrFail('Master.adminAuthority.frontCode.All');
        /** @var \App\Model\Entity\Admin $admin */
        $admin = $this->commonData()->getAdminLoginData();

        // 共通の権限追加
        $accessData = array_merge($accessData, Configure::readOrFail('Master.admin.canAccessAuthority.common'));

        // マスター管理者の権限追加
        if ($admin->isMasterAdmin()) {
            $accessData = array_merge($accessData, Configure::readOrFail('Master.admin.canAccessAuthority.master'));
        }

        // 各種設定メニューと運用メニューがなしの場合
        if ($adminAuthoritiesData === null) {
            return $accessData;
        }

        // 権限分けデータ成型
        if (!$admin->isOperatorAdmin()) {
            if (!empty($adminAuthoritiesData->get('access_setting'))) {
                if (in_array($all, $adminAuthoritiesData->get('access_setting'))) {
                    // 全て以外のデータを格納
                    foreach (array_keys(Configure::readOrFail('Master.adminAuthority.accessSetting')) as $keys) {
                        if ($keys !== $all) {
                            $accessData[] = $keys;
                        }
                    }
                } else {
                    foreach ($adminAuthoritiesData->get('access_setting') as $value) {
                        $accessData[] = $value;
                    }
                }
            }
        }

        if (!empty($adminAuthoritiesData->get('access_operator'))) {
            if (in_array($all, $adminAuthoritiesData->get('access_operator'))) {
                // 全て以外のデータを格納
                foreach (array_keys(Configure::readOrFail('Master.adminAuthority.accessOperator')) as $keys) {
                    if ($keys !== $all) {
                        // 操作ログ権限除去(オペレータ管理者)
                        if ($admin->isOperatorAdmin() && $keys === 'AdminOperationalLogs__all') {
                            continue;
                        }
                        $accessData[] = $keys;
                    }
                }
            } else {
                foreach ($adminAuthoritiesData->get('access_operator') as $value) {
                    // 操作ログ権限除去(オペレータ管理者)
                    if ($admin->isOperatorAdmin() && $value === 'AdminOperationalLogs__all') {
                        continue;
                    }
                    $accessData[] = $value;
                }
            }
        }

        // 追加の権限
        foreach (Configure::readOrFail('Master.adminAuthority.addAuthority') as $key => $authority) {
            if (in_array($key, $accessData)) {
                $accessData = array_merge($accessData, $authority);
            }
        }

        return $accessData;
    }
}
