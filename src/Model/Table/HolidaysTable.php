<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\ImportableTableInterface;
use App\Utility\ArrayUtility;
use App\Utility\DateTimeUtility;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * Holidays Model
 *
 * @method \App\Model\Entity\Holiday newEmptyEntity()
 * @method \App\Model\Entity\Holiday newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Holiday[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Holiday get($primaryKey, $options = [])
 * @method \App\Model\Entity\Holiday findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Holiday patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Holiday[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Holiday|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Holiday saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Holiday[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Holiday[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Holiday[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Holiday[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class HolidaysTable extends AppTable implements ImportableTableInterface
{
    /**
     * @var array|null
     */
    protected $saveManyErrors = null;

    /**
     * @var array|null
     */
    protected $holidays = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
            'afterSave' => false,
        ]);
    }

    /**
     * Model.beforeSaveManyイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSaveMany(EventInterface $event, array $entities, $options)
    {
        $this->saveManyErrors = null;

        //追加削除した日程に予約が存在するかチェック
        $checkDate = [];
        foreach ($entities as $entity) {
            if ($entity instanceof EntityInterface) {
                $date = DateTimeUtility::convertToDateObject($entity->get('date'));
                if (!isset($date)) {
                    throw new CakeException();
                }

                $checkDate[$date->format('Y/m/d')] = $date->format('Y/m/d');
            }
        }

        $month = Hash::get($options, 'month');
        if (is_null($month) || !$month instanceof FrozenTime) {
            return false;
        }

        $holidayList = $this->find('holiday', [
            'from' => $month->firstOfMonth(),
            'to' => $month->lastOfMonth(),
        ])->toArray();

        $existingDate = [];
        foreach ($holidayList as $date) {
            $existingDate[$date] = $date;
        }

        $diffArr = Hash::diff($checkDate, $existingDate);
        if (!empty($diffArr)) {
            $errorMessage = $this->isReserve($diffArr);
            if (!empty($errorMessage)) {
                $this->saveManyErrors = $errorMessage;
            }
        }

        if ($this->checkEntityErrors($entities) || !empty($this->saveManyErrors)) {
            return false;
        }

        // 登録済みの日付へID設定
        foreach ($entities as $entity) {
            $date = DateTimeUtility::convertToDateObject($entity->get('date'));
            if (!isset($date)) {
                throw new CakeException();
            }
            $entity->set([
                'date' => $date->format('Y/m/d'),
            ], ['setter' => false]);

            $holidayId = ArrayUtility::arraySearch($date->format('Y/m/d'), $holidayList);
            if ($holidayId !== false) {
                $entity->set('id', $holidayId);
                $entity->setNew(false);
            } else {
                $entity->unset('id');
                $entity->setNew(true);
            }
        }

        return true;
    }

    /**
     * 予約の存在チェック
     *
     * @param array $dates チェックする日付
     * @return array エラーメッセージ
     */
    public function isReserve(array $dates)
    {
        /** @var \App\Model\Table\EventsTable $eventTable */
        $eventTable = $this->getTableLocator()->get('Events');
        $events = $eventTable->find('searchList')
            ->formatResults(function (\Cake\Collection\CollectionInterface $results) {
                $eventData['over'] = [];
                $eventData['notOver'] = [];

                foreach ($results->toArray() as $row) {
                    if ($row['time_from'] >= $row['time_to']) {
                        $eventData['over'][] = $row['id'];
                    } else {
                        $eventData['notOver'][] = $row['id'];
                    }
                }

                return $eventData;
            }, true);

        $events = $events->toArray();

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        $result = $reservationsTable->isReserveInHolidays($dates, $events['over'], true);
        $errorDates = $result->toArray();

        $result = $reservationsTable->isReserveInHolidays($dates, $events['notOver'], false);
        $errorDates += $result->toArray();

        $errorMessage = [];
        foreach ($errorDates as $errorDate) {
            $errorMessage[] = __(Message::ERROR_NOT_UPDATE_HOLIDAYS, $errorDate->get('id'));
        }

        return $errorMessage;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function ($entity, $options) {
            if (Hash::get($options, 'method') === 'import') {
                // テンポラリテーブルを作成 1トランザクションで1つのみ作成するため処理が終わると削除する

                /** @var \App\Model\Table\TempDatesTable $tempDatesTable */
                $tempDatesTable = $this->getTableLocator()->get('TempDates', ['noCreate' => true]);
                $tempDatesTable->createTempTable();
                $date = $entity->get('date')->format('Y/m/d');
                $errorMessage = $this->isReserve([$date => $date]);
                $tempDatesTable->dropTempTable();

                if (!empty($errorMessage)) {
                    $entity->setError('date', $errorMessage);

                    return false;
                }
            }

            return true;
        }, 'isReserve');

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('date', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyDate('date')
            ->add('date', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        return $validator;
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
        $month = $options['month'];
        $form = $month->startOfMonth()->format('Y-m-d');
        $to = $month->endOfMonth()->format('Y-m-d');

        $condition = [
            [$this->excludeQueryByEntities($entities)],
            [
                'Holidays.date >=' => $form,
                'Holidays.date <=' => $to,
            ],
        ];

        $this->deleteAll($condition);

        return true;
    }

    /**
     * 入力値のフィルター
     *
     * @param array $inputs getRequest()
     * @return array
     */
    public function filterInputDate($inputs)
    {
        if (isset($inputs['holidays']) && is_array($inputs['holidays'])) {
            foreach ($inputs['holidays'] as $index => $holiday) {
                if (empty($holiday['date'])) {
                    unset($inputs['holidays'][$index]);
                }
            }
        } else {
            $inputs['holidays'] = [];
        }

        return $inputs;
    }

    /**
     * 月を設定する
     *
     * @param array|string|null $month 月
     * @return \Cake\I18n\FrozenTime
     */
    public function setMonth($month = null)
    {
        if (is_string($month) && Validation::notBlank($month) && CustomValidation::date($month, 'ymd')) {
            $month = FrozenTime::createFromFormat('Y/m/d', $month);
        } else {
            $month = FrozenTime::now();
        }

        return $month;
    }

    /**
     * エンティティを取得
     *
     * @param \Cake\I18n\FrozenTime $month 月
     * @return array Entityを返却
     */
    public function getEntities(FrozenTime $month)
    {
        $form = $month->startOfMonth()->format('Y/m/d');
        $to = $month->endOfMonth()->format('Y/m/d');

        $query = $this->find('all')->select(['Holidays.id', 'Holidays.date'])
            ->where([
                'Holidays.date >=' => $form,
                'Holidays.date <=' => $to,
            ]);

        $return = [];
        foreach ($query as $row) {
            $return[$row['date']->format('Y/m/d')] = $row;
        }

        return $return;
    }

    /**
     * チェックされた日付を取得
     *
     * @param array $entities エンティティ
     * @return array
     */
    public function getInputDates(array $entities)
    {
        $dates = [];
        foreach ($entities as $entity) {
            $date = DateTimeUtility::convertToDateObject($entity->get('date'));
            if (isset($date)) {
                $dates[$date->format('Y/m/d')] = $entity->get('id');
            }
        }

        return $dates;
    }

    /**
     * エラーを設定
     *
     * @param array $holidayInputs 入力値
     * @param array $entities エンティティ
     * @param array $calender カレンダー
     * @return array Entityを返却
     */
    public function setHolidayError($holidayInputs, $entities, $calender)
    {
        $flashErrors = [];
        $inputList = array_column($holidayInputs['holidays'], 'date', 'index');
        foreach ($entities as $entity) {
            $error = $entity->getErrors();
            if (!empty($error['date'])) {
                $invalid = $entity->getInvalid('date');
                $key = ArrayUtility::arraySearch($invalid['date'], $inputList);
                $flashErrors[] = $calender[$key]->format('Y/m/d') . ':' . array_pop($error['date']);
            }
        }
        if (!empty($this->saveManyErrors) && is_array($this->saveManyErrors)) {
            foreach ($this->saveManyErrors as $message) {
                $flashErrors[] = $message;
            }
        }

        return $flashErrors;
    }

    /**
     * 前後年分の一覧を取得
     *
     * @param \Cake\I18n\FrozenTime $month 月
     * @return array
     */
    public function getList3years(FrozenTime $month)
    {
        $from = $month->subYears(1)->startOfYear()->format('Y-m-d');
        $to = $month->addYears(1)->endOfYear()->format('Y-m-d');

        $query = $this->find('all');
        $query
            ->select([
                'Holidays.date',
                'year' => 'Holidays.date',
            ])
            ->where([
                'Holidays.date >=' => $from,
                'Holidays.date <=' => $to,
            ])
            ->orderAsc('date');

        $list = [];
        $count = [];
        foreach ($query as $row) {
            $list[$row->year->format('Y')][] = $row->date->format('m/d');

            if (isset($count[$row->year->format('Y')])) {
                $count[$row->year->format('Y')]++;
            } else {
                $count[$row->year->format('Y')] = 1;
            }
        }

        if (!empty($count)) {
            $list['count'] = max($count);
        } else {
            $list['count'] = 0;
        }

        $list['previous'] = $month->subYears(1)->format('Y');
        $list['current'] = $month->format('Y');
        $list['next'] = $month->addYears(1)->format('Y');

        return $list;
    }

    /**
     * 祝日ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findHoliday(Query $query, array $options)
    {
        $query
            ->select([
                'id',
                'date',
            ])
            ->where([
                'date >=' => $options['from'],
                'date <=' => $options['to'],
            ])
            ->enableHydration(false)
            ->orderAsc('date');

        $query->formatResults(function (\Cake\Collection\CollectionInterface $results) {
            $date = [];
            foreach ($results as $row) {
                $date[$row['id']] = $row['date']->format('Y/m/d');
            }

            return $date;
        });

        return $query;
    }

    /**
     * カレンダー生成時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCalendar(Query $query, array $options)
    {
        $query->select([
            'date',
        ]);

        $dateFrom = Hash::get($options, 'inputs.date_from');
        if (isset($dateFrom) && $dateFrom !== '') {
            $query->where([
                'Holidays.date >=' => $dateFrom,
            ]);
        }
        $dateTo = Hash::get($options, 'inputs.date_to');
        if (isset($dateTo) && $dateTo !== '') {
            $query->where([
                'Holidays.date <=' => $dateTo,
            ]);
        }

        $query->order([
            'date' => 'ASC',
        ]);

        $query->enableHydration(false);

        $query->formatResults(function ($results) {
            $holidays = [];
            foreach ($results as $data) {
                $date = new FrozenDate($data['date']);
                $holidays[$date->format('Y-m-d')] = $date;
            }

            return $holidays;
        });

        return $query;
    }

    /**
     * 指定年の祝日を取得
     *
     * @param int $yearFrom 開始
     * @param int $yearTo 終了
     * @return array
     */
    public function getHolidaysByYear($yearFrom, $yearTo)
    {
        if (!isset($this->holidays)) {
            $this->holidays = [];
        }

        $holidays = [];
        for ($year = $yearFrom; $year <= $yearTo; ++$year) {
            if (!isset($this->holidays[$year])) {
                $this->holidays[$year] = $this->find('calendar', [
                    'inputs' => [
                        'date_from' => $year . '/01/01',
                        'date_to' => $year . '/12/31',
                    ],
                ])->toArray();
            }
            $holidays += $this->holidays[$year];
        }

        return $holidays;
    }

    /**
     * CSVファイルを生成
     *
     * @return string ファイルパス
     */
    public function createCsv()
    {
        $header = Configure::readOrFail('Setting.csv.download.holiday.header');

        $query = $this->find('all')->select(['Holidays.date'])
            ->orderAsc('date');

        $filePath = $this->createCsvFile($header, function () use ($query) {
            foreach ($query as $holiday) {
                $data = [
                    'date' => $holiday->date,
                ];
                yield $data;
            }
        });

        return $filePath;
    }

    /**
     * フォーマットダウンロード用CSV
     *
     * @return string
     */
    public function createSampleCsv()
    {
        $header = Configure::readOrFail('Setting.csv.download.holiday.header');

        $filePath = $this->createCsvFile($header, function () {
            $description = Configure::readOrFail('Setting.csv.import.sample');

            yield ['date' => $description['date']];
        });

        return $filePath;
    }

    /**
     * @inheritDoc
     */
    public function tryLockForImport()
    {
        return $this->tryLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_HOLIDAY);
    }

    /**
     * @inheritDoc
     */
    public function getLockForImport()
    {
        $this->getLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_HOLIDAY);
    }

    /**
     * @inheritDoc
     */
    public function releaseLockForImport()
    {
        $this->releaseLock(static::LOCK_TYPE_CSV_IMPORT, static::IMPORT_LOCK_HOLIDAY);
    }
}
