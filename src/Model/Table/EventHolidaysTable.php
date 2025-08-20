<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Table\Traits\EventTrait;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * EventHolidays Model
 *
 * @method \App\Model\Entity\EventHoliday newEmptyEntity()
 * @method \App\Model\Entity\EventHoliday newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventHoliday[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventHoliday get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventHoliday findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventHoliday patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventHoliday[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventHoliday|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHoliday saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventHoliday[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHoliday[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHoliday[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventHoliday[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventHolidaysTable extends AppTable
{
    use EventTrait;

    public const SETTING_ALL_HOLIDAYS = null;

    /**
     * @var array|null
     */
    protected $allHoliday = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'LEFT',
        ]);

        $this->hasMany('EventHolidayExcludeDates', [
            'foreignKey' => 'event_holiday_id',
            'saveStrategy' => 'replace',
            'cascadeCallbacks' => true,
            'dependent' => true,
        ]);

        $this->hasMany('EventHolidayWeeks', [
            'foreignKey' => 'event_holiday_id',
            'saveStrategy' => 'replace',
            'cascadeCallbacks' => true,
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => false,
            'afterSave' => false,
        ]);
    }

    /**
     * afterSaveMany hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities entities
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterSaveMany(EventInterface $event, array $entities, ArrayObject $options)
    {
        $condition = [
            [$this->excludeQueryByEntities($entities)],
            [
                'event_id IS NULL',
            ],
        ];

        $deleteEntities = $this->find('all', ['conditions' => $condition]);
        foreach ($deleteEntities as $deleteEntity) {
            $this->delete($deleteEntity, ['checkRules' => false]);
        }

        return true;
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
    protected function buildDefaultFieldValues()
    {
        $default = [
            'event_holiday' => [
                'event_holiday' => [
                    'date_from' => '',
                    'date_to' => '',
                ],
            ],
        ];

        return $default;
    }

    /**
     * 全体設定の定数を取得
     *
     * @return string
     */
    public function getConstSettingAll()
    {
        return static::SETTING_ALL_HOLIDAYS;
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
        $this->holidayStockSettingBeforeSave($entity);
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addUpdate(function ($entity) {
            $excludeDates = $entity->get('event_holiday_exclude_dates');
            $date = Hash::combine($excludeDates, '{*}.date', '{*}.date');

            if (count($excludeDates) !== count(array_unique($date))) {
                $entity->setError('event_holiday_exclude_dates', (string)__(Message::ERROR_DUPLICATION));

                return false;
            }

            return true;
        }, 'excludeDatesDuplicated');

        return $rules;
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
        if (!isset($data['event_holiday_exclude_dates']) || !is_array($data['event_holiday_exclude_dates'])) {
            $data->offsetSet('event_holiday_exclude_dates', []);
        }
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->holidayStockSettingValidator($validator);

        $validator->requirePresence('event_holiday_weeks', false)
            ->allowEmptyArray('event_holiday_weeks')
            ->array('event_holiday_weeks', __(Message::ERROR_INVALID_VALUE));

        $validator->requirePresence('event_holiday_exclude_dates', false)
            ->allowEmptyArray('event_holiday_exclude_dates')
            ->array('event_holiday_exclude_dates', __(Message::ERROR_INVALID_VALUE));

        return $validator;
    }

    /**
     * 編集のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query
            ->select(['id', 'event_id', 'date_from', 'date_to', 'time_from', 'time_to'])
            ->contain([
                'EventHolidayExcludeDates' => [
                    'fields' => ['id', 'event_holiday_id', 'date'],
                ],
                'EventHolidayWeeks' => [
                    'fields' => ['id', 'event_holiday_id', 'week'],
                    'sort' => ['EventHolidayWeeks.week' => 'ASC'],
                ],
            ], true);

        if ($options['eventId'] === null) {
            $query->where(['event_id IS NULL']);
        } else {
            $query->where(['event_id' => $options['eventId']]);
        }

        return $query;
    }

    /**
     * 休日のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findHoliday(Query $query, array $options)
    {
        $query
            ->contain([
                'EventHolidayExcludeDates' => [],
                'EventHolidayWeeks' => [
                    'sort' => ['EventHolidayWeeks.week' => 'ASC'],
                ],
            ], true);

        $eventID = Hash::get($options, 'inputs.event_id');
        if (isset($eventID) && $eventID !== '' && count((array)$eventID) > 0) {
            $query
                ->where(['EventHolidays.event_id IN' => $eventID]);
        }

        $query
            ->order([
                'EventHolidays.id' => 'ASC',
            ], true);

        return $query;
    }

    /**
     * 予約枠データを取得
     *
     * @param int|null $id ID
     * @return array|\Cake\Datasource\EntityInterface データ
     */
    public function getEventData($id)
    {
        if ($id === null) {
            $event['event_id'] = static::SETTING_ALL_HOLIDAYS;
            $event['name'] = '全ての予約枠';
        } else {
            /** @var \App\Model\Table\EventsTable $eventTable */
            $eventTable = $this->getTableLocator()->get('Events');
            $event = $eventTable->find('all')->select(['id', 'name'])
                ->enableHydration(false)->where(['id' => $id])->first();

            if (isset($event)) {
                $event['event_id'] = $event['id'];
            } else {
                $event['event_id'] = null;
            }
        }

        return $event;
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->select(['id', 'date_from', 'date_to', 'time_from', 'time_to', 'event_id'])
            ->contain([
                'Events' => ['fields' => ['id', 'name']],
                'EventHolidayExcludeDates' => [
                    'fields' => ['id', 'event_holiday_id', 'date'],
                ],
                'EventHolidayWeeks' => [
                    'fields' => ['id', 'event_holiday_id', 'week'],
                ],
            ]);

        $query->join([
            'Events' => [
                'table' => 'events',
                'type' => 'LEFT',
                'conditions' => 'Events.id = EventHolidays.event_id',
            ],
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Events.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'EventHolidays.event_id IS NULL' => 'DESC',
                    'EventHolidays.' . $sort => $direction,
                ] + [
                    'EventHolidays.id' => $direction,
                ], true);

        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');

        return $eventsTable->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 全体設定取得用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAllHoliday(Query $query, array $options)
    {
        $query->select([
            'id',
            'date_from',
            'date_to',
            'time_from',
            'time_to',
        ]);
        $query->contain([
            'EventHolidayWeeks' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'week',
                ],
            ],
            'EventHolidayExcludeDates' => [
                'fields' => [
                    'id',
                    'event_holiday_id',
                    'date',
                ],
            ],
        ]);
        $query->where([
            $query->newExpr()->isNull('EventHolidays.event_id'),
        ]);
        $query->order([
            'EventHolidays.id' => 'ASC',
        ]);

        return $query;
    }

    /**
     * 全体設定を取得
     *
     * @return array
     */
    public function getAllHoliday()
    {
        if (!isset($this->allHoliday)) {
            $this->allHoliday = $this->find('allHoliday')->toArray();
        }

        return $this->allHoliday;
    }

    /**
     * 初期遷移時の整形
     *
     * @param array $entities エンティティ複数
     * @return void
     */
    public function formatDefault($entities)
    {
        foreach ($entities as $entity) {
            /** @var \App\Model\Table\EventHolidayWeeksTable $eventHolidayWeeksTable */
            $eventHolidayWeeksTable = $this->getTableLocator()->get('EventHolidayWeeks');

            $formatEntityWeeks = $eventHolidayWeeksTable->formatDefaultWeeks($entity->get('event_holiday_weeks'));
            $entity->set('event_holiday_weeks', $formatEntityWeeks);
        }
    }

    /**
     * 配列データのキー振り直し
     *
     * @param array $reArray 配列
     * @param mixed $keys 振り直しを実施するキー
     * @param array $options オプション
     * @return array
     */
    public function rebalanceArrayKey(array $reArray, $keys, array $options = [])
    {
        $reArray = parent::rebalanceArrayKey($reArray, $keys);

        if (isset($reArray['event_holidays']) && is_array($reArray['event_holidays'])) {
            foreach ($reArray['event_holidays'] as $index => $eventHolidays) {
                $reArray['event_holidays'][$index] = parent::rebalanceArrayKey(
                    $eventHolidays,
                    'event_holiday_exclude_dates'
                );
            }
        }

        return $reArray;
    }
}
