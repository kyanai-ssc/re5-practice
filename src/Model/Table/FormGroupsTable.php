<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\FormGroup;
use App\Model\Entity\SmartLock;
use App\Model\InputType\InputTypeManagerTrait;
use App\Utility\SmartLock\SmartLockLinkage;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * FormGroups Model
 *
 * @method \App\Model\Entity\FormGroup newEmptyEntity()
 * @method \App\Model\Entity\FormGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormGroupsTable extends AppTable
{
    use InputTypeManagerTrait;

    public const GROUP_NAME_MAX = 100;

    /**
     * @var array|null
     */
    protected $cacheData = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('FormItems', [
            'foreignKey' => 'form_group_id',
            'dependent' => false,
            'saveStrategy' => 'append',
            'sort' => [
                'FormItems.sort_no' => 'ASC',
                'FormItems.id' => 'ASC',
            ],
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('form_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('form_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('form_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('formType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

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
                    'rule' => ['maxLength', static::GROUP_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::GROUP_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('name_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('name_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('name_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('nameDisplayFlg'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('form_items', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('form_items', __(Message::ERROR_NOT_EMPTY), false)
            ->add('form_items', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'formType' => Configure::readOrFail('Master.form.formType'),
            'nameDisplayFlg' => Configure::readOrFail('Master.form.nameDisplayFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * Model.beforeFindイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\ORM\Query $query クエリ
     * @param \ArrayObject $options オプション
     * @param bool $primary プライマリー
     * @return void
     */
    public function beforeFind(EventInterface $event, Query $query, ArrayObject $options, bool $primary)
    {
        if (!Hash::get($options, 'includeSmartLockTypeNotNull', false)) {
            $smartLock = new SmartLockLinkage();
            if (!$smartLock->useAkerun()) {
                $query->where([
                    'FormGroups.smart_lock_type IS' => null,
                ]);
            }
        }
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
            'FormItems' => [],
            'FormItems.FormItemChoices' => [],
            'FormItems.FormItemDetails' => [],
            'FormItems.FormItemOptionGroups' => [],
            'FormItems.FormItemOptionGroups.FormItemOptions' => [],
        ], true);

        $query->order([
            'FormGroups.sort_no' => 'ASC',
            'FormGroups.id' => 'ASC',
        ], true);

        $formType = (array)Hash::get($options, 'inputs.form_type', []);
        if (count($formType) > 0) {
            $query->where([
                'FormGroups.form_type IN' => $formType,
            ]);
        }

        $query->formatResults(function ($formGroups) {
            $result = $formGroups->map(function ($formGroup) {
                $formItems = [];
                if ($formGroup->has('form_items')) {
                    foreach ($formGroup->get('form_items') as $formItem) {
                        $formItem->set('session_key', $formItem->get('id'));
                        $formItems[$formItem->get('session_key')] = $formItem->toArray();
                    }
                }
                $formGroup->set('form_items_data', $formItems);

                return $formGroup;
            });

            return $result;
        });

        return $query;
    }

    /**
     * フォームグループ取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFormGroups(Query $query, array $options)
    {
        $query->contain([
            'FormItems' => [],
            'FormItems.FormItemChoices' => [],
            'FormItems.FormItemDetails' => [],
            'FormItems.FormItemOptionGroups' => [],
            'FormItems.FormItemOptionGroups.FormItemOptions' => [],
        ], true);

        $query->order([
            'FormGroups.sort_no' => 'ASC',
            'FormGroups.id' => 'ASC',
        ], true);

        $formType = (array)Hash::get($options, 'inputs.form_type', []);
        if (count($formType) > 0) {
            $query->where([
                'FormGroups.form_type IN' => $formType,
            ]);
        }

        $query->formatResults(function ($formGroups) {
            $isAdmin = $this->commonData()->existsAdminLoginData();

            $result = $formGroups->map(function ($formGroup) use ($isAdmin) {
                $formItems = $formGroup->get('form_items');
                if (is_array($formItems)) {
                    foreach ($formItems as $formItem) {
                        $inputTypeManager = $this->inputTypeManager($formItem->get('input_type'), $isAdmin);
                        $formItem->setInputTypeItem(
                            $inputTypeManager->createItem($formGroup->get('form_type'), $formItem)
                        );
                    }
                }

                return $formGroup;
            });

            return $result;
        });

        return $query;
    }

    /**
     * Model.beforeSaveManyイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSaveMany(EventInterface $event, array $entities, ArrayObject $options)
    {
        $this->getBehavior('AdminOperationLog')->setConfig([
            'afterSave' => false,
        ]);

        $this->assignSortNo($entities);
        if ($this->checkEntityErrors($entities)) {
            return false;
        }

        $this->deleteByFormType((int)Hash::get($options, 'formType'), $entities);

        return true;
    }

    /**
     * Model.afterSaveManyCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSaveManyCommit(EventInterface $event, array $entities, ArrayObject $options)
    {
        $this->deleteCacheData();
    }

    /**
     * フォーム種別を検証
     *
     * @param mixed $formType フォーム種別
     * @return bool 検証結果
     */
    public function validateFormType($formType)
    {
        $validator = new Validator();
        $validator
            ->requirePresence('form_type', true)
            ->allowEmptyString('form_type')
            ->add('form_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('formType'))],
                    'last' => true,
                ],
            ]);

        $errors = $validator->validate(['form_type' => $formType]);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * フォームグループ一覧を取得
     *
     * @param int|null $formType フォーム種別
     * @param array $options option
     * @return array フォームグループ一覧
     */
    public function getFormGroups(?int $formType = null, array $options = [])
    {
        $key = 'user';
        if ($this->commonData()->existsAdminLoginData()) {
            $key = 'admin';
        }

        if (!isset($this->cacheData[$key])) {
            $formGroups = Cache::remember($key, function () {
                $query = $this->find('formGroups');

                return $query->toArray();
            }, 'formGroups');

            $cacheData = [];
            foreach ($formGroups as $formGroup) {
                $cacheData[''][] = $formGroup;
                $cacheData[$formGroup->get('form_type')][] = $formGroup;
            }

            $this->cacheData[$key] = $cacheData;
        }

        $result = $this->cacheData[$key][$formType];

        $excludeUserData = Hash::get($options, 'excludeUserData', false);
        if ($excludeUserData) {
            foreach ($result as $formGroupIndex => $formGroup) {
                $formItems = (array)$formGroup->get('form_items');
                foreach ($formItems as $formItemIndex => $formItem) {
                    if (!$formItem->isDefaultItem()) {
                        unset($formItems[$formItemIndex]);
                    }
                }

                if (!empty($formItems)) {
                    $formGroup = clone $formGroup;
                    $formGroup->set('form_items', $formItems);
                    $formGroup->clean();
                    $result[$formGroupIndex] = $formGroup;
                } else {
                    unset($result[$formGroupIndex]);
                }
            }
        }

        if (Hash::get($options, 'cloneItem', false)) {
            foreach ($result as $formGroupIndex => $formGroup) {
                $formItems = (array)$formGroup->get('form_items');
                foreach ($formItems as $formItemIndex => $formItem) {
                    $formItems[$formItemIndex] = clone $formItem;
                }

                $formGroup = clone $formGroup;
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();
                $result[$formGroupIndex] = $formGroup;
            }
        }

        return $result;
    }

    /**
     * IDを維持してエンティティーを生成
     *
     * @param array $entities エンティティー配列
     * @param mixed $data データ
     * @param array $options オプション
     * @return array エンティティー配列
     */
    public function patchEntitiesPreserveId(array $entities, $data, array $options = [])
    {
        if (!is_array($data)) {
            throw new BadRequestException();
        }
        foreach ($data as $formGroupData) {
            if (!is_array($formGroupData)) {
                throw new BadRequestException();
            }
        }

        $allFormItems = $options['formItems'];
        foreach ($data as $formGroupIndex => $formGroupData) {
            if (!isset($formGroupData['form_items']) || !is_array($formGroupData['form_items'])) {
                $formGroupData['form_items'] = [];
            }
            foreach ($formGroupData['form_items'] as $formItemIndex => $formItemData) {
                if (
                    !is_array($formItemData) || !isset($formItemData['session_key'])
                    || !is_scalar($formItemData['session_key']) || !isset($allFormItems[$formItemData['session_key']])
                ) {
                    throw new BadRequestException();
                }
                $formGroupData['form_items'][$formItemIndex] = $allFormItems[$formItemData['session_key']];
                unset($allFormItems[$formItemData['session_key']]);
            }
            $data[$formGroupIndex] = $formGroupData;
        }
        if (!empty($allFormItems)) {
            throw new BadRequestException();
        }

        $smartLock = new SmartLockLinkage();
        // Akerun が有効ではない場合、項目が削除されるのを防ぐため Akerun用の入力項目を追加する
        if ((string)Hash::get($options, 'formType') === (string)FormGroup::FORM_TYPE_USER && !$smartLock->useAkerun()) {
            $akerunGroups = $this->getAkerunFormGroup();
            foreach ($akerunGroups as $akerunGroup) {
                $data[] = $akerunGroup->toArray();
            }
        }

        // テーブルごとに既存エンティティーを分類
        $original = [];
        foreach ($entities as $entity) {
            foreach ($this->extractEntity($entity) as $childEntity) {
                $original[$childEntity->getSource()][$childEntity->get('id')] = $childEntity;
            }
        }

        // エンティティ生成処理
        $marshall = function ($alias, $id, $data, $original, $options = []) {
            $entity = null;
            $table = $this->getTableLocator()->get($alias);
            $options = ['associated' => []] + $options;
            if (!is_scalar($id) || !isset($original[$alias][$id])) {
                $entity = $table->newEntity($data, $options);
            } else {
                $entity = $table->patchEntity($original[$alias][$id], $data, $options);
            }

            return $entity;
        };

        // フォームグループ
        $formGroups = [];
        foreach ($data as $formGroupIndex => $formGroupData) {
            $formGroupId = Hash::get($formGroupData, 'id');
            $formGroups[$formGroupIndex] = call_user_func(
                $marshall,
                'FormGroups',
                $formGroupId,
                $formGroupData,
                $original
            );
            $formGroups[$formGroupIndex]->set('form_type', Hash::get($options, 'formType'));

            // フォーム項目
            $formItems = [];
            foreach ($formGroupData['form_items'] as $formItemIndex => $formItemData) {
                $formItemId = Hash::get($formItemData, 'id');
                $formItems[$formItemIndex] = call_user_func(
                    $marshall,
                    'FormItems',
                    $formItemId,
                    $formItemData,
                    $original
                );
                $formItems[$formItemIndex]->set('form_group_id', $formGroupId);

                // フォーム項目選択肢
                $formItemChoices = [];
                $formItemChoicesData = [];
                if (isset($formItemData['form_item_choices'])) {
                    $formItemChoicesData = $formItemData['form_item_choices'];
                }
                foreach ($formItemChoicesData as $formItemChoiceIndex => $formItemChoiceData) {
                    $formItemChoiceId = Hash::get($formItemChoiceData, 'id');
                    $formItemChoices[$formItemChoiceIndex] = call_user_func(
                        $marshall,
                        'FormItemChoices',
                        $formItemChoiceId,
                        $formItemChoiceData,
                        $original
                    );
                    $formItemChoices[$formItemChoiceIndex]->set('form_item_id', $formItemId);
                }
                $formItemChoicesErrors = $formItems[$formItemIndex]->getError('form_item_choices');
                $formItems[$formItemIndex]->set('form_item_choices', $formItemChoices);
                if (count($formItemChoicesErrors) > 0) {
                    $formItems[$formItemIndex]->setError('form_item_choices', $formItemChoicesErrors);
                }

                // フォーム項目詳細
                $formItemDetails = [];
                $formItemDetailsData = [];
                if (isset($formItemData['form_item_details'])) {
                    $formItemDetailsData = $formItemData['form_item_details'];
                }
                foreach ($formItemDetailsData as $formItemDetailIndex => $formItemDetailData) {
                    $formItemDetailId = Hash::get($formItemDetailData, 'id');
                    $formItemDetails[$formItemDetailIndex] = call_user_func(
                        $marshall,
                        'FormItemDetails',
                        $formItemDetailId,
                        $formItemDetailData,
                        $original,
                        ['inputType' => Hash::get($formItemData, 'input_type')]
                    );
                    $formItemDetails[$formItemDetailIndex]->set('form_item_id', $formItemId);
                }
                $formItemDetailsErrors = $formItems[$formItemIndex]->getError('form_item_details');
                $formItems[$formItemIndex]->set('form_item_details', $formItemDetails);
                if (count($formItemDetailsErrors) > 0) {
                    $formItems[$formItemIndex]->setError('form_item_details', $formItemDetailsErrors);
                }

                // フォーム項目オプショングループ
                $formItemOptionGroup = null;
                if (isset($formItemData['form_item_option_group'])) {
                    $formItemOptionGroupData = $formItemData['form_item_option_group'];
                    $formItemOptionGroupId = Hash::get($formItemOptionGroupData, 'id');
                    $formItemOptionGroup = call_user_func(
                        $marshall,
                        'FormItemOptionGroups',
                        $formItemOptionGroupId,
                        $formItemOptionGroupData,
                        $original
                    );
                    $formItemOptionGroup->set('form_item_id', $formItemId);

                    // フォーム項目オプション
                    $formItemOptions = [];
                    $formItemOptionsData = [];
                    if (isset($formItemOptionGroupData['form_item_options'])) {
                        $formItemOptionsData = $formItemOptionGroupData['form_item_options'];
                    }
                    foreach ($formItemOptionsData as $formItemOptionIndex => $formItemOptionData) {
                        $formItemOptionId = Hash::get($formItemOptionData, 'id');
                        $formItemOptions[$formItemOptionIndex] = call_user_func(
                            $marshall,
                            'FormItemOptions',
                            $formItemOptionId,
                            $formItemOptionData,
                            $original
                        );
                        $formItemOptions[$formItemOptionIndex]->set(
                            'form_item_option_group_id',
                            $formItemOptionGroupId
                        );
                    }
                    $formItemOptionsErrors = $formItemOptionGroup->getError('form_item_options');
                    $formItemOptionGroup->set('form_item_options', $formItemOptions);
                    if (count($formItemOptionsErrors) > 0) {
                        $formItemOptionGroup->setError('form_item_options', $formItemOptionsErrors);
                    }
                }
                $formItemOptionGroupErrors = $formItems[$formItemIndex]->getError('form_item_option_group');
                $formItems[$formItemIndex]->set('form_item_option_group', $formItemOptionGroup);
                if (count($formItemOptionGroupErrors) > 0) {
                    $formItems[$formItemIndex]->setError('form_item_option_group', $formItemOptionGroupErrors);
                }
            }
            $formItemsErrors = $formGroups[$formGroupIndex]->getError('form_items');
            $formGroups[$formGroupIndex]->set('form_items', $formItems);
            if (count($formItemsErrors) > 0) {
                $formGroups[$formGroupIndex]->setError('form_items', $formItemsErrors);
            }
        }

        return $formGroups;
    }

    /**
     * 表示順を割り当てる
     *
     * @param array $entities エンティティー配列
     * @return void
     */
    public function assignSortNo(array $entities)
    {
        $sortNo = 0;
        foreach ($entities as $entity) {
            $sortNo += 1;
            $entity->set('sort_no', $sortNo);
        }

        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getAssociation('FormItems')->getTarget();

        foreach ($entities as $entity) {
            if ($entity->has('form_items')) {
                $formItemsTable->assignSortNo($entity->get('form_items'));
            }
        }
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array|null $excludeEntities エンティティー配列
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeEntities = null)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getAssociation('FormItems')->getTarget();

        $formItems = [];
        if (isset($excludeEntities)) {
            foreach ($excludeEntities as $entity) {
                if ($entity->has('form_items')) {
                    foreach ($entity->get('form_items') as $formItem) {
                        $formItems[] = $formItem;
                    }
                }
            }
        }

        $formItemsTable->deleteByFormType($formType, $formItems);

        $this->deleteAll([
            'FormGroups.form_type' => $formType,
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('admin', 'formGroups');
        Cache::delete('user', 'formGroups');
        $this->cacheData = null;
    }

    /**
     * Akerunユーザー情報のフォームグループデータを取得する
     *
     * @return \Cake\ORM\Query
     */
    public function getAkerunFormGroup()
    {
        return $this->find('edit', [
                'inputs' => [
                    'form_type' => FormGroup::FORM_TYPE_USER,
                ],
                'includeSmartLockTypeNotNull' => true,
            ])
            ->contain('FormItems')
            ->where([
                'FormGroups.smart_lock_type' => SmartLock::TYPE_AKERUN,
            ]);
    }

    /**
     * フォームグループ一覧を取得する
     *
     * @param array $options オプション
     * @return array エンティティー配列
     */
    public function getFormGroupList(array $options = [])
    {
        $result = [];
        $all = Hash::get($options, 'inputs.all');
        if (!empty($all)) {
            $key = Configure::readOrFail('Master.formPattern.selectGroupAll.key');
            $value = Configure::readOrFail('Master.formPattern.selectGroupAll.value');
            $result[$key] = $value;
        }
        $query = $this->find();
        $formType = (array)Hash::get($options, 'inputs.form_type', []);
        if (count($formType) > 0) {
            $query->where([
                'form_type IN' => $formType,
            ]);
        }
        $query->order([
            'sort_no' => 'ASC',
            'id' => 'ASC',
        ], true);

        if ($query->count() > 0) {
            foreach ($query->toArray() as $data) {
                $result[$data->get('id')] = $data->get('name');
            }
        }

        return $result;
    }
}
