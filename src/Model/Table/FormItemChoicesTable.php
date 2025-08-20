<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;

/**
 * FormItemChoices Model
 *
 * @method \App\Model\Entity\FormItemChoice newEmptyEntity()
 * @method \App\Model\Entity\FormItemChoice newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemChoice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormItemChoice get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormItemChoice findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormItemChoice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemChoice[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormItemChoice|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemChoice saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormItemChoice[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemChoice[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemChoice[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormItemChoice[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormItemChoicesTable extends AppTable
{
    public const CHOICE_NAME_MAX = 100;

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
                    'rule' => ['maxLength', static::CHOICE_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CHOICE_NAME_MAX),
                ],
            ]);

        return $validator;
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
        if (isset($data['name']) && is_string($data['name'])) {
            $data->offsetSet('name', $this->csvFormat()->replaceSeparetorForMultiple($data['name']));
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
            ['FormItemChoices.form_item_id IN' => $formItemsQuery],
            $this->excludeQueryByEntities($excludeEntities),
        ]);
    }
}
