<?php
declare(strict_types=1);

namespace App\Model\Table\Traits;

use App\Locale\Message;
use App\Utility\ArrayUtility;
use App\Validation\CustomValidation;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validation;

/**
 * 予約枠関連共通処理
 * Even trait.
 */
trait EventTrait
{
    /**
     * @var array|null
     */
    protected $holidays = null;

    /**
     * @param \Cake\Validation\Validator $validator validator
     * @param bool $stock イレギュラー設定
     * @return \Cake\Validation\Validator $validator
     */
    public function holidayStockSettingValidator(Validator $validator, bool $stock = false)
    {
        $validator->requirePresence('date_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date_from')
            ->add('date_from', [
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

        $validator->requirePresence('date_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date_to')
            ->add('date_to', [
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
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'date_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
            ]);

        $validator->requirePresence('time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('time_from')
            ->add('time_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
            ]);

        $validator->requirePresence('time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyTime('time_to')
            ->add('time_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'time' => [
                    'rule' => [
                        'time24h',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_TIME),
                ],
            ]);

        if ($stock) {
            $validator
                ->requirePresence('stock', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('stock', __(Message::ERROR_NOT_EMPTY), false)
                ->add('stock', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'naturalNumber' => [
                        'rule' => ['naturalNumber', true],
                        'last' => true,
                        'message' => __(Message::ERROR_NUMBER),
                    ],
                    'lessThanOrEqual' => [
                        'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * @param \Cake\Validation\Validator $validator validator
     * @param bool $stock イレギュラー設定
     * @return \Cake\Validation\Validator $validator
     */
    public function holidayStockSettingWeeksValidator($validator, bool $stock = false)
    {
        $validator
            ->requirePresence('week', false)
            ->allowEmptyString('week')
            ->add('week', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('week')),
                    ],
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
     * @param \Cake\Validation\Validator $validator validator
     * @param bool $stock イレギュラー設定
     * @return \Cake\Validation\Validator $validator
     */
    public function holidayStockSettingExDatesValidator($validator, bool $stock = false)
    {
        $validator
            ->requirePresence('date', false)
            ->allowEmptyString('date', __(Message::ERROR_NOT_EMPTY), false)
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
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        return $validator;
    }

    /**
     * イレギュラー・休日設定のbeforeSave共通処理
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param bool $stock イレギュラー設定
     * @return void
     */
    public function holidayStockSettingBeforeSave(EntityInterface $entity, bool $stock = false)
    {
        $weekTable = 'event_holiday_weeks';
        $excludeTable = 'event_holiday_exclude_dates';

        if ($stock) {
            $weekTable = str_replace('event_holiday', 'event_stock_setting', $weekTable);
            $excludeTable = str_replace('event_holiday', 'event_stock_setting', $excludeTable);
        }

        $originalValues = $entity->extractOriginal($entity->getVisible());
        $weeks = $entity->get($weekTable);
        if (is_array($weeks)) {
            foreach ($weeks as $key => $week) {
                if (empty($week->get('week'))) {
                    unset($entity->$weekTable[$key]);
                    continue;
                }

                if (!$entity->isNew()) {
                    $idKey = ArrayUtility::arraySearch(
                        $week['week'],
                        array_column($originalValues[$weekTable], 'week')
                    );

                    if ($idKey !== false) {
                        $week->set('id', $originalValues[$weekTable][$idKey]['id']);
                        $week->setNew(false);
                    }
                }
            }
        }
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
     * 休日情報の取得
     *
     * @param \Cake\I18n\FrozenTime $dateFrom From
     * @param \Cake\I18n\FrozenTime $dateTo To
     * @return array|null
     */
    public function getHolidays($dateFrom, $dateTo)
    {
        if (!is_null($this->holidays)) {
            return $this->holidays;
        }

        /** @var \App\Model\Table\HolidaysTable $holidayTable */
        $holidayTable = $this->getTableLocator()->get('Holidays');
        $holidays = $holidayTable->find('holiday', ['from' => $dateFrom, 'to' => $dateTo])->toArray();
        $holidays = array_flip($holidays);

        $this->holidays = $holidays;

        return $holidays;
    }
}
