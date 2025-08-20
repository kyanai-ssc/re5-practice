<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;

/**
 * ReservationEventPlans Model
 *
 * @method \App\Model\Entity\ReservationEventPlan newEmptyEntity()
 * @method \App\Model\Entity\ReservationEventPlan newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationEventPlan get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationEventPlan|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationEventPlan[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationEventPlansTable extends AppTable
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
        $this->belongsTo('EventPlans', [
            'foreignKey' => 'event_plan_id',
            'joinType' => 'INNER',
        ]);
    }
}
