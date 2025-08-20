<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use Cake\Validation\Validator;

/**
 * EventHolidayExcludeDates Model
 *
 * @method \App\Model\Entity\EventHolidayExcludeDate newEmptyEntity()
 * @method \App\Model\Entity\EventHolidayExcludeDate newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayExcludeDate[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventHolidayExcludeDatesTable extends AppTable
{
    use EventTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('EventHolidays', [
            'foreignKey' => 'event_holiday_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $this->holidayStockSettingExDatesValidator($validator);
    }
}
