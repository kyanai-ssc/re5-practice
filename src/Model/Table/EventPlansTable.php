<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Event as EventEntity;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * EventPlans Model
 *
 * @method \App\Model\Entity\EventPlan newEmptyEntity()
 * @method \App\Model\Entity\EventPlan newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventPlan[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventPlan get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventPlan findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventPlan patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventPlan[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventPlan|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventPlan saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventPlan[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventPlan[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventPlan[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventPlan[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventPlansTable extends AppTable
{
    public const NAME_MAX = 100;
    public const USAGE_TIME_MAX = 100000;
    public const CHARGE_MAX = 10000000;

    public const CSV_COLUMN_ID = 'id';
    public const CSV_COLUMN_NAME = 'name';
    public const CSV_COLUMN_EVENT_ID = 'event_id';
    public const CSV_COLUMN_USAGE_TIME = 'usage_time';
    public const CSV_COLUMN_USAGE_DAY = 'usage_day';
    public const CSV_COLUMN_CHARGE = 'charge';
    public const CSV_COLUMN_PUBLIC_FLG = 'public_flg';
    public const CSV_COLUMN_EVENT = 'event';

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
        $this->hasMany('ReservationEventPlans', [
            'foreignKey' => 'event_plan_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'publicFlg' => Configure::readOrFail('Master.eventPlans.publicFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('public_flg', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('public_flg')
            ->add('public_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('publicFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

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
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('charge', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('charge', __(Message::ERROR_NOT_EMPTY), false)
            ->add('charge', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'nonNegativeInteger' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::CHARGE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CHARGE_MAX),
                ],
            ]);

        $validator
            ->requirePresence('usage_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_time', __(Message::ERROR_NOT_EMPTY), function ($context) {
                $type = Hash::get($context, 'data.event_type');
                if (is_scalar($type) && (string)$type === (string)EventEntity::TYPE_TIME) {
                    return false;
                }

                return true;
            })
            ->add('usage_time', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'greaterThanOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        EventEntity::USETIME_INTERVAL,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_MINIMUM_UNIT, EventEntity::USETIME_INTERVAL),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, EventsTable::USAGE_TIME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, EventsTable::USAGE_TIME_MAX),

                ],
            ]);

        $validator
            ->requirePresence('usage_day', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_day', __(Message::ERROR_NOT_EMPTY), function ($context) {
                $type = Hash::get($context, 'data.event_type');
                if (is_scalar($type) && (string)$type === (string)EventEntity::TYPE_DAY) {
                    return false;
                }

                return true;
            })
            ->add('usage_day', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, EventsTable::UNIT_DAY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, EventsTable::UNIT_DAY_MAX),

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
     * CSVへ出力するデータを生成
     *
     * @param array|null $csvItems 出力項目
     * @param array $options オプション
     * @return string CSV1セルの情報
     */
    public function generateCsvData($csvItems, array $options = [])
    {
        $csvData = [];

        if (empty($csvItems)) {
            return '';
        }

        $asHeader = $options['asHeader'];

        foreach ($csvItems as $csv) {
            $multipleData = [];
            foreach ($asHeader as $column) {
                $multipleData[] = $this->formatCsvData($column, $options + ['plans' => $csv]);
            }
            $csvData[] = $this->csvFormat()->csvForMultiple($multipleData, false);
        }

        return $this->csvFormat()->csvForHasMany($csvData);
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param string $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatCsvData(string $column, array $options)
    {
        $eventPlans = $options['plans'];
        $valueOptions = $this->getFieldValueOptions();

        $data = $eventPlans->get($column);
        $value = '';
        if (!is_null($data)) {
            switch ($column) {
                case static::CSV_COLUMN_PUBLIC_FLG:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['publicFlg'][$data]);
                    break;
                default:
                    $value = $data;
                    break;
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('id')
            ->value('event_id')
            ->like('event_name', [
                'before' => true,
                'after' => true,
                'fields' => ['events.name'],
            ]);
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

        $query->select([
            'id',
            'event_id',
            'name',
            'Events.id',
            'Events.name',
            'Events.label_id',
            'Labels.id',
            'Labels.parent_id',
        ])
        ->contain([
            'Events' => [
                'Labels',
            ],
        ]);

        $query->join([
            'Events' => [
                'table' => 'events',
                'type' => 'LEFT',
                'conditions' => 'Events.id = EventPlans.event_id',
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

        $order = [
            'EventPlans.' . $sort => $direction,
        ];

        $query
            ->order($order + [
                    'EventPlans.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 予約アップロードフォーマット用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findReservationUploadFormat(Query $query, array $options)
    {
        $query->select([
            'id',
            'event_id',
            'name',
            'usage_time',
            'usage_day',
            'charge',
        ]);
        $query->contain([
            'Events' => [
                'fields' => [
                    'id',
                    'name',
                    'type',
                ],
            ],
        ]);
        $query->order([
            'Events.sort_no' => 'ASC',
            'Events.id' => 'ASC',
            'EventPlans.sort_no' => 'ASC',
            'EventPlans.id' => 'ASC',
        ]);

        return $query;
    }
}
