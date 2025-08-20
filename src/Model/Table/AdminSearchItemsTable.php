<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * AdminSearchItems Model
 *
 * @method \App\Model\Entity\AdminSearchItem newEmptyEntity()
 * @method \App\Model\Entity\AdminSearchItem newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminSearchItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminSearchItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminSearchItem findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminSearchItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminSearchItem[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminSearchItem|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminSearchItem saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminSearchItem[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminSearchItem[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminSearchItem[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminSearchItem[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminSearchItemsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys(Configure::readOrFail('Master.adminSearchItems.type'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('items', false)
            ->allowEmptyArray('items')
            ->add('items', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => function ($value, $context) {
                        $type = Hash::get($context['data'], 'type');
                        $valueOptions = $this->getFieldValueOptions('items');

                        foreach ($value as $formType => $items) {
                            if (!isset($valueOptions[$type][$formType]) || !is_array($items)) {
                                return false;
                            }
                            if (!Validation::multiple($items, ['in' => array_keys($valueOptions[$type][$formType])])) {
                                return false;
                            }
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('items', 'json');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'items' => [
                AdminSearchItem::TYPE_USER_LIST => $this->buildUserListValueOptions(),
                AdminSearchItem::TYPE_RESERVATION_LIST => $this->buildReservationListValueOptions(),
                AdminSearchItem::TYPE_MAIL_DELIVERIES => $this->buildMailDeliveriesValueOptions(),
                AdminSearchItem::TYPE_RESERVATION_USER => $this->buildRegisterReservationValueOptions(),
            ],
        ];

        return $fieldValueOptions;
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
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        $query->where(['AdminSearchItems.admin_id' => $loginData->get('id')]);

        $type = (array)Hash::get($options, 'inputs.type', []);
        if (count($type) > 0) {
            $query->where(['AdminSearchItems.type IN' => $type]);
        }

        return $query;
    }

    /**
     * フォーム項目取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFormItem(Query $query, array $options)
    {
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        $query->select([
            'id',
            'type',
            'items',
        ]);

        $query->where(['AdminSearchItems.admin_id' => $loginData->get('id')]);

        $type = (array)Hash::get($options, 'inputs.type', []);
        if (count($type) > 0) {
            $query->where(['AdminSearchItems.type IN' => $type]);
        }

        $query->formatResults(function ($adminSearchItems) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            $result = $adminSearchItems->map(function ($adminSearchItem) use ($formItemsTable) {
                $data = $adminSearchItem->get('items');
                if (!is_array($data)) {
                    $data = [];
                }

                $valueOptions = [];
                foreach ($this->getFieldValueOptions('items.' . $adminSearchItem->get('type')) as $items) {
                    foreach ($items as $key => $value) {
                        $valueOptions[$key] = $value;
                    }
                }

                $result = [];
                foreach ($data as $formType => $items) {
                    foreach ($items as $item) {
                        if (isset($valueOptions[$item])) {
                            if (Configure::check('Master.adminSearchItems.items.' . $item)) {
                                $result[$formType][] = $item;
                            } else {
                                $formItem = $formItemsTable->getFormItem($item);
                                if (isset($formItem)) {
                                    $result[$formType][] = $formItem;
                                }
                            }
                        }
                    }
                }

                return $result;
            });

            return $result;
        });

        return $query;
    }

    /**
     * 初期登録データのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefaultData(Query $query, array $options)
    {
        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');

        $query->select([
            'id',
            'type',
            'items',
        ]);
        $query->where([
            'AdminSearchItems.admin_id' => $adminsTable->find('initialAdmin'),
        ]);
        $query->order([
            'AdminSearchItems.type' => 'ASC',
            'AdminSearchItems.id' => 'ASC',
        ]);

        $query->formatResults(function ($items) {
            $result = [];
            foreach ($items as $item) {
                $result[$item->get('type')] = $item->get('items');
            }

            return $result;
        });

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
        if (!isset($data['items']) || !is_array($data['items'])) {
            $data->offsetSet('items', []);
        }
    }

    /**
     * 初期登録データを生成
     *
     * @return array 初期登録データ
     */
    public function createDefaultData()
    {
        $data = $this->find('defaultData')->toArray();
        if (empty($data)) {
            $data = [
                AdminSearchItem::TYPE_USER_LIST => $this->createUserListDefaultData(),
                AdminSearchItem::TYPE_RESERVATION_LIST => $this->createReservationListDefaultData(),
                AdminSearchItem::TYPE_MAIL_DELIVERIES => $this->createMailDeliveriesDefaultData(),
                AdminSearchItem::TYPE_RESERVATION_USER => $this->createRegisterReservationDefaultData(),
            ];
        }

        $defaultData = [];
        foreach ($data as $type => $items) {
            $defaultData[] = $this->newEntity([
                'type' => $type,
                'items' => $items,
            ], ['validate' => false, 'accessibleFields' => ['type' => true]]);
        }

        return $defaultData;
    }

    /**
     * 顧客一覧の項目リストを生成
     *
     * @param bool $isUserOnly 会員情報のみ取得
     * @param bool $isFormItemOnly フォーム項目のみ取得
     * @return array 項目リスト
     */
    protected function buildUserListValueOptions($isUserOnly = false, $isFormItemOnly = false)
    {
        if ($isUserOnly) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            $valueOptions = [];
            $formTypes = [FormGroup::FORM_TYPE_USER];
            foreach ($formTypes as $formType) {
                if ($isFormItemOnly) {
                    foreach (Configure::readOrFail('Master.adminSearchItems.formTypeItems.' . $formType) as $item) {
                        $valueOptions[$formType][$item] = Configure::readOrFail(
                            'Master.adminSearchItems.items.' . $item
                        );
                    }
                }
                foreach ($formItemsTable->getSearchableFormItems($formType) as $formItems) {
                    foreach ($formItems as $formItem) {
                        $valueOptions[$formType][$formItem->get('id')] = $formItem->get('name') ?? '　';
                    }
                }
            }

            $list = $valueOptions;
        } else {
            $list = $this->buildReservationListValueOptions();
        }

        return $list;
    }

    /**
     * 予約一覧の項目リストを生成
     *
     * @return array 項目リスト
     */
    protected function buildReservationListValueOptions()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $valueOptions = [];
        $formTypes = [FormGroup::FORM_TYPE_USER, FormGroup::FORM_TYPE_RESERVATION];
        foreach ($formTypes as $formType) {
            foreach (Configure::readOrFail('Master.adminSearchItems.formTypeItems.' . $formType) as $item) {
                $valueOptions[$formType][$item] = Configure::readOrFail('Master.adminSearchItems.items.' . $item);
            }
            foreach ($formItemsTable->getSearchableFormItems($formType) as $formItems) {
                foreach ($formItems as $formItem) {
                    $valueOptions[$formType][$formItem->get('id')] = $formItem->get('name') ?? '　';
                }
            }
        }

        // 決済を利用しない場合には表示しない項目
        if (!$systemSettingsTable->getData()->usePayment()) {
            // 決済方法
            unset($valueOptions[FormGroup::FORM_TYPE_RESERVATION][AdminSearchItem::ITEM_PAYMENT_METHOD]);
            // 決済ステータス
            unset($valueOptions[FormGroup::FORM_TYPE_RESERVATION][AdminSearchItem::ITEM_PAYMENT_STATUS]);
            // 決済連携状況
            unset($valueOptions[FormGroup::FORM_TYPE_RESERVATION][AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS]);
        }

        return $valueOptions;
    }

    /**
     * メール配信履歴の項目リストを生成
     *
     * @return array 項目リスト
     */
    protected function buildMailDeliveriesValueOptions()
    {
        return $this->buildUserListValueOptions(true);
    }

    /**
     * 予約登録編集の項目リストを生成
     *
     * @return array 項目リスト
     */
    protected function buildRegisterReservationValueOptions()
    {
        return $this->buildUserListValueOptions(true);
    }

    /**
     * 顧客一覧の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createUserListDefaultData()
    {
        return $this->createReservationListDefaultData();
    }

    /**
     * 予約一覧の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createReservationListDefaultData()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $data = [];
        foreach (Configure::readOrFail('Master.adminSearchItems.formTypeItems') as $formType => $items) {
            foreach ($items as $item) {
                $data[$formType][] = $item;
            }
        }
        foreach ($formItemsTable->getSearchableFormItems() as $formType => $formItems) {
            foreach ($formItems as $formItem) {
                if ($formItem->isDefaultItem()) {
                    $data[$formType][] = $formItem->get('id');
                }
            }
        }

        return $data;
    }

    /**
     * メール配信履歴の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createMailDeliveriesDefaultData()
    {
        return $this->createUserListDefaultData();
    }

    /**
     * メール配信履歴の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createRegisterReservationDefaultData()
    {
        return $this->createUserListDefaultData();
    }

    /**
     * entityの生成
     *
     * @param int $adminId 管理者ID
     * @param mixed $type タイプ
     * @return \Cake\Datasource\EntityInterface 初期登録データ
     */
    public function createNewEntity(int $adminId, $type)
    {
        $entity = $this->newEntity(['admin_id' => $adminId, 'type' => $type]);

        $entity->set('admin_id', $adminId);
        $entity->set('type', $type);

        return $entity;
    }
}
