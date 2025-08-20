<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * EventHolidayWeeks Model
 *
 * @method \App\Model\Entity\EventHolidayWeek newEmptyEntity()
 * @method \App\Model\Entity\EventHolidayWeek newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayWeek get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventHolidayWeek|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHolidayWeek[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventHolidayWeeksTable extends AppTable
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
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'week' => Configure::readOrFail('Master.event.week'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $this->holidayStockSettingWeeksValidator($validator);
    }
}
