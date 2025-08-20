<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\AdminListItem;
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
 * AdminListItems Model
 *
 * @method \App\Model\Entity\AdminListItem newEmptyEntity()
 * @method \App\Model\Entity\AdminListItem newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminListItem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminListItem get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminListItem findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminListItem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminListItem[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminListItem|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminListItem saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminListItem[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminListItem[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminListItem[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminListItem[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminListItemsTable extends AppTable
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
                    'rule' => ['inList', array_keys(Configure::readOrFail('Master.adminListItems.type'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('items', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('items', __(Message::ERROR_NOT_EMPTY_SELECT), false)
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

                        return Validation::multiple($value, ['in' => array_keys($valueOptions[$type])]);
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
                AdminListItem::TYPE_USER_LIST => $this->buildUserListValueOptions(),
                AdminListItem::TYPE_RESERVATION_LIST => $this->buildReservationListValueOptions(),
                AdminListItem::TYPE_MAIL_DELIVERIES => $this->buildMailDeliveriesValueOptions(),
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

        $query->where(['AdminListItems.admin_id' => $loginData->get('id')]);

        $type = (array)Hash::get($options, 'inputs.type', []);
        if (count($type) > 0) {
            $query->where(['AdminListItems.type IN' => $type]);
        }

        $query->formatResults(function ($adminListItems) {
            $result = $adminListItems->map(function ($adminListItem) {
                if (is_array($adminListItem->get('items'))) {
                    $valueOptions = $this->getFieldValueOptions('items.' . $adminListItem->get('type'));
                    $items = [];
                    foreach ($adminListItem->get('items') as $item) {
                        if (isset($valueOptions[$item])) {
                            $items[] = $item;
                        }
                    }
                    $adminListItem->set('items', $items);
                    $adminListItem->clean();
                }

                return $adminListItem;
            });

            return $result;
        });

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

        $query->where(['AdminListItems.admin_id' => $loginData->get('id')]);

        $type = (array)Hash::get($options, 'inputs.type', []);
        if (count($type) > 0) {
            $query->where(['AdminListItems.type IN' => $type]);
        }

        $query->formatResults(function ($adminListItems) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            $result = $adminListItems->map(function ($adminListItem) use ($formItemsTable) {
                $data = $adminListItem->get('items');
                if (!is_array($data)) {
                    $data = [];
                }

                $valueOptions = $this->getFieldValueOptions('items.' . $adminListItem->get('type'));

                $result = [];
                foreach ($data as $item) {
                    if (isset($valueOptions[$item])) {
                        if (Configure::check('Master.adminListItems.items.' . $item)) {
                            $result[] = $item;
                        } else {
                            $formItem = $formItemsTable->getFormItem($item);
                            if (isset($formItem)) {
                                $result[] = $formItem;
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
            'AdminListItems.admin_id' => $adminsTable->find('initialAdmin'),
        ]);
        $query->order([
            'AdminListItems.type' => 'ASC',
            'AdminListItems.id' => 'ASC',
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
                AdminListItem::TYPE_USER_LIST => $this->createUserListDefaultData(),
                AdminListItem::TYPE_RESERVATION_LIST => $this->createReservationListDefaultData(),
                AdminListItem::TYPE_MAIL_DELIVERIES => $this->createMailDeliveriesDefaultData(),
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
     * @return array 項目リスト
     */
    protected function buildUserListValueOptions()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $valueOptions = [];
        $formTypes = [FormGroup::FORM_TYPE_USER];
        foreach ($formTypes as $formType) {
            foreach (Configure::readOrFail('Master.adminListItems.formTypeItems.' . $formType) as $item) {
                $valueOptions[$item] = Configure::readOrFail('Master.adminListItems.items.' . $item);
            }
            foreach ($formItemsTable->getListDisplayableFormItems($formType) as $formItems) {
                foreach ($formItems as $formItem) {
                    $valueOptions[$formItem->get('id')] = $formItem->get('name') ?? '　';
                }
            }
        }

        return $valueOptions;
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
            foreach (Configure::readOrFail('Master.adminListItems.formTypeItems.' . $formType) as $item) {
                $valueOptions[$item] = Configure::readOrFail('Master.adminListItems.items.' . $item);
            }
            foreach ($formItemsTable->getListDisplayableFormItems($formType) as $formItems) {
                foreach ($formItems as $formItem) {
                    $valueOptions[$formItem->get('id')] = $formItem->get('name') ?? '　';
                }
            }
        }

        // 決済を利用しない場合には表示しない項目
        if (!$systemSettingsTable->getData()->usePayment()) {
            // 決済方法
            unset($valueOptions[AdminListItem::ITEM_PAYMENT_METHOD]);
            // 決済ステータス
            unset($valueOptions[AdminListItem::ITEM_PAYMENT_STATUS]);
            // 決済連携状況
            unset($valueOptions[AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS]);
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
        return $this->buildUserListValueOptions();
    }

    /**
     * 顧客一覧の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createUserListDefaultData()
    {
        $valueOptions = $this->getFieldValueOptions('items');
        $data = array_keys($valueOptions[AdminListItem::TYPE_USER_LIST]);

        return $data;
    }

    /**
     * 予約一覧の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createReservationListDefaultData()
    {
        $valueOptions = $this->getFieldValueOptions('items');
        $data = array_keys($valueOptions[AdminListItem::TYPE_RESERVATION_LIST]);

        return $data;
    }

    /**
     * メール配信履歴の初期登録データを生成
     *
     * @return array 初期登録データ
     */
    protected function createMailDeliveriesDefaultData()
    {
        $valueOptions = $this->getFieldValueOptions('items');
        $data = array_keys($valueOptions[AdminListItem::TYPE_MAIL_DELIVERIES]);

        return $data;
    }
}
