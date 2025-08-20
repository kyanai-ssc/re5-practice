<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use Cake\Validation\Validator;

/**
 * EventStockSettingExcludeDates Model
 *
 * @method \App\Model\Entity\EventStockSettingExcludeDate newEmptyEntity()
 * @method \App\Model\Entity\EventStockSettingExcludeDate newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSettingExcludeDate[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventStockSettingExcludeDatesTable extends AppTable
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
    public function validationDefault(Validator $validator): Validator
    {
        return $this->holidayStockSettingExDatesValidator($validator, true);
    }
}
