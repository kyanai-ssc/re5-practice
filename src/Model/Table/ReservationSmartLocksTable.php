<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ReservationSmartLocks Model
 *
 * @method \App\Model\Entity\ReservationSmartLock newEmptyEntity()
 * @method \App\Model\Entity\ReservationSmartLock newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationSmartLock get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationSmartLock|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationSmartLock[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationSmartLocksTable extends AppTable
{
    /**
     * @var array|null
     */
    protected $errorMessages = null;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('reservation_id', 'create')
            ->notEmptyString('reservation_id');

        $validator
            ->scalar('smart_lock_user_id')
            ->allowEmptyString('smart_lock_user_id');

        $validator
            ->scalar('smart_lock_grant_id')
            ->allowEmptyString('smart_lock_grant_id');

        $validator
            ->scalar('smart_lock_pin')
            ->allowEmptyString('smart_lock_pin');

        $validator
            ->scalar('smart_lock_key_url')
            ->allowEmptyString('smart_lock_key_url');

        $validator
            ->scalar('smart_lock_key_id')
            ->allowEmptyString('smart_lock_key_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('reservation_id', 'Reservations'), ['errorField' => 'reservation_id']);

        return $rules;
    }

    /**
     * 予約時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservation(Query $query, array $options)
    {
        $query->select([
            'reservation_id',
            'smart_lock_pin',
            'smart_lock_key_url',
        ]);

        $reservationId = Hash::get($options, 'inputs.reservation_id');
        $query->where([
            'reservation_id' => $reservationId,
        ]);

        return $query;
    }
}
