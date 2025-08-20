<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * EventStockSettings Model
 *
 * @method \App\Model\Entity\EventStockSetting newEmptyEntity()
 * @method \App\Model\Entity\EventStockSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventStockSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventStockSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventStockSettingsTable extends AppTable
{
    use EventTrait;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('EventStockSettingExcludeDates', [
            'foreignKey' => 'event_stock_setting_id',
            'saveStrategy' => 'replace',
            'cascadeCallbacks' => true,
            'dependent' => true,
        ]);
        $this->hasMany('EventStockSettingWeeks', [
            'foreignKey' => 'event_stock_setting_id',
            'saveStrategy' => 'replace',
            'cascadeCallbacks' => true,
            'dependent' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addUpdate(function ($entity) {
            $excludeDates = $entity->get('event_stock_setting_exclude_dates');
            $date = Hash::combine($excludeDates, '{*}.date', '{*}.date');

            if (count($excludeDates) !== count(array_unique($date))) {
                $entity->setError('event_stock_setting_exclude_dates', (string)__(Message::ERROR_DUPLICATION));

                return false;
            }

            return true;
        }, 'excludeDatesDuplicated');

        return $rules;
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $this->holidayStockSettingBeforeSave($entity, true);
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
        if (
            !isset($data['event_stock_setting_exclude_dates'])
            || !is_array($data['event_stock_setting_exclude_dates'])
        ) {
            $data->offsetSet('event_stock_setting_exclude_dates', []);
        }
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->holidayStockSettingValidator($validator, true);

        $validator->requirePresence('event_stock_setting_weeks', false)
            ->allowEmptyArray('event_stock_setting_weeks')
            ->array('event_stock_setting_weeks', __(Message::ERROR_INVALID_VALUE));

        $validator->requirePresence('event_stock_setting_exclude_dates', false)
            ->allowEmptyArray('event_stock_setting_exclude_dates')
            ->array('event_stock_setting_exclude_dates', __(Message::ERROR_INVALID_VALUE));

        return $validator;
    }

    /**
     * 初期遷移時の整形
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @return void
     */
    public function formatDefault($entity)
    {
        /** @var \App\Model\Table\EventStockSettingWeeksTable $eventStockSettingWeeksTable */
        $eventStockSettingWeeksTable = $this->getTableLocator()->get('EventStockSettingWeeks');

        $formatEntityWeeks = $eventStockSettingWeeksTable->formatDefaultWeeks(
            $entity->get('event_stock_setting_weeks')
        );
        $entity->set('event_stock_setting_weeks', $formatEntityWeeks);
    }
}
