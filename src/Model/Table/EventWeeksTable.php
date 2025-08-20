<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * EventWeeks Model
 *
 * @method \App\Model\Entity\EventWeek newEmptyEntity()
 * @method \App\Model\Entity\EventWeek newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventWeek[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventWeek get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventWeek findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventWeek patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventWeek[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventWeek|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventWeek saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventWeek[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventWeek[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventWeek[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventWeek[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventWeeksTable extends AppTable
{
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
        $validator
            ->requirePresence('week', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('week', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('week', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('week'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                    'on' => function ($context) {
                        return !empty(Hash::get($context['data'], 'week'));
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * 曜日のチェックを外した場合の予約存在チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @return bool
     */
    public function enableDeleteWeek(EntityInterface $entity)
    {
        $originalData = $entity->extractOriginal(['event_weeks']);
        $originalWeeks = array_column($originalData['event_weeks'], 'week');

        $eventWeeks = $entity->get('event_weeks');
        if (!is_array($eventWeeks)) {
            $eventWeeks = array_keys(Configure::read('Master.event.week'));
        } else {
            $eventWeeks = array_column($eventWeeks, 'week');
            //全て未選択の場合は、全選択と同様
            if (empty(array_filter($eventWeeks))) {
                $eventWeeks = array_keys(Configure::read('Master.event.week'));
            }
        }

        if (empty($originalWeeks)) {
            $originalWeeks = array_keys(Configure::read('Master.event.week'));
        }

        $diffWeek = [];
        foreach ($originalWeeks as $originalWeek) {
            if (ArrayUtility::arraySearch($originalWeek, $eventWeeks) === false) {
                $diffWeek[] = $originalWeek;
            }
        }

        if (empty($diffWeek)) {
            return true;
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $result = $reservationsTable->isReserveInWeeks(
            [
                'date_from' => $entity->get('date_from'),
                'date_to' => $entity->get('date_to'),
                'weeks' => $diffWeek,
                'event_id' => $entity->get('id'),
                'time_from' => $entity->get('time_from'),
                'time_to' => $entity->get('time_to'),
            ]
        );

        return $result;
    }

    /**
     * Entityのキーを曜日ごとに
     *
     * @param array $entity エンティティ
     * @return mixed
     */
    public function formatDefaultWeeks(array $entity)
    {
        $formatEntity = [];
        foreach ($entity as $week) {
            $formatEntity[$week->get('week') - 1] = $week;
        }

        return $formatEntity;
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

        $valueOptions = $this->getFieldValueOptions();
        foreach ($csvItems as $csv) {
            $csvData[] = $this->csvFormat()->csvForId($csv->get('week'), $valueOptions['week'][$csv->get('week')]);
        }

        return $this->csvFormat()->csvForHasMany($csvData);
    }
}
