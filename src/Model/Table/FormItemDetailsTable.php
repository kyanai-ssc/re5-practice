<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\InputType\InputTypeManagerTrait;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * FormItemDetails Model
 *
 * @method \App\Model\Entity\FormItemDetail newEmptyEntity()
 * @method \App\Model\Entity\FormItemDetail newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormItemDetail findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormItemDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemDetail[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemDetail|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemDetail saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemDetail[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemDetail[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemDetail[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemDetail[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormItemDetailsTable extends AppTable
{
    use InputTypeManagerTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'textInputTranslate' => Configure::readOrFail('Master.form.textInputTranslate'),
            'textInputCheck' => Configure::readOrFail('Master.form.textInputCheck'),
            'dateUpperLimitType' => Configure::readOrFail('Master.form.dateUpperLimitType'),
        ];

        return $fieldValueOptions;
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
        if (Hash::get($options, 'validate', true) === true) {
            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getAssociation('FormItems')->getTarget();

            $inputType = Hash::get($options, 'inputType');
            if ($formItemsTable->validateInputType($inputType)) {
                $isAdmin = $this->commonData()->existsAdminLoginData();

                $validator = $this->createValidator(static::DEFAULT_VALIDATOR);
                $validator->setProvider(static::VALIDATOR_PROVIDER_NAME, $this);
                $validator = $this->inputTypeManager((int)$inputType, $isAdmin)->validationFormItemDetail($validator);
                $this->setValidator('inputType', $validator);
                $options->offsetSet('validate', 'inputType');
            }
        }
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
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $this->deleteAll([
            ['FormItemDetails.form_item_id IN' => $formItemsQuery],
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }
}
