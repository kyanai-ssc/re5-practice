<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;

/**
 * ReservationAdditions Model
 *
 * @method \App\Model\Entity\ReservationAddition newEmptyEntity()
 * @method \App\Model\Entity\ReservationAddition newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationAddition[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationAddition get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationAddition findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationAddition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationAddition[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationAddition|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationAddition saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationAddition[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationAddition[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationAddition[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationAddition[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationAdditionsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array $excludeFormItemId 除外するフォーム項目ID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemId = null)
    {
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $where = [
            'ReservationAdditions.form_item_id IN' => $formItemsQuery,
        ];
        if (!empty($excludeFormItemId)) {
            $where['ReservationAdditions.form_item_id NOT IN'] = (array)$excludeFormItemId;
        }
        $this->deleteAll($where);
    }

    /**
     * 添付ファイルのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFile(Query $query, array $options)
    {
        return $query->where([
            'reservation_id' => $options['reservation_id'],
            'form_item_id' => $options['form_item_id'],
        ]);
    }
}
