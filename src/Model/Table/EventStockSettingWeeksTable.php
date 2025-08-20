<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * EventStockSettingWeeks Model
 *
 * @method \App\Model\Entity\EventStockSettingWeek newEmptyEntity()
 * @method \App\Model\Entity\EventStockSettingWeek newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingWeek[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventStockSettingWeeksTable extends AppTable
{
    use EventTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('EventStockSettings', [
            'foreignKey' => 'event_stock_setting_id',
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
        return $this->holidayStockSettingWeeksValidator($validator, true);
    }
}
