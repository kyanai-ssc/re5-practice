<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\Event;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\ChargeTypeInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * ReservationTime class.
 */
class ReservationTime extends AbstractInputTypeItem implements
    ChargeTypeInterface,
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    MailOutputInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use MailOutputTrait;

    /**
     * @var string
     */
    protected $tableName = 'reservations';

    /**
     * @var string
     */
    protected $delimiter = "\n";

    /**
     * @inheritDoc
     */
    public function getFieldsetInputKey()
    {
        $fieldsetInputKey = [
            'usage_time' => $this->getTableName() . '.usage_time',
            'usage_day' => $this->getTableName() . '.usage_day',
            'plan_values' => $this->getTableName() . '.plan_values',
        ];

        return $fieldsetInputKey;
    }

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        $event = $this->getConfig('event');
        if (!$this->getConfig('isApp', false)) {
            if (!$event->canHideReservationTimeInput($this->isAdmin())) {
                $this->displayType['canDisplay'] = true;
                $this->displayType['canInput'] = true;
            }
        }
        $reservation = $this->getConfig('reservation');
        if (isset($reservation) && (string)$reservation->getOriginal('event_id') === (string)$event->get('id')) {
            if ((string)$event->get('time_plan') === ((string)Event::PLAN_SINGLE)) {
                $timeValueOptions = $event->getUsageTimeValueOptions($this->isAdmin());
                $dayValueOptions = $event->getUsageDayValueOptions($this->isAdmin());
                if (
                    !isset($timeValueOptions[$reservation->get('usage_time')])
                    || !isset($dayValueOptions[$reservation->get('usage_day')])
                ) {
                    $this->displayType['canInput'] = false;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }
        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        $data = null;
        $valueOptions = [];
        if ((string)$event->get('time_plan') === ((string)Event::PLAN_SINGLE)) {
            $columns = [
                Event::TYPE_TIME => 'usage_time',
                Event::TYPE_DAY => 'usage_day',
            ];

            $data = $reservation->get($columns[$event->get('type')]);
            if ((string)$event->get('type') === ((string)Event::TYPE_TIME)) {
                $valueOptions = $this->getUsageTimeValueOptions($event);
            }
            if ((string)$event->get('type') === ((string)Event::TYPE_DAY)) {
                $valueOptions = $this->getUsageDayValueOptions($event);
            }
        }
        if ((string)$event->get('time_plan') === ((string)Event::PLAN_MULTIPLE)) {
            $data = $reservation->get('plan_values');
            $valueOptions = $this->getEventPlansValueOptions($event);
        }

        $value = array_intersect_key($valueOptions, array_fill_keys((array)$data, true));

        return implode($this->delimiter, $value);
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $event = $this->getConfig('event');
        if ((string)$event->get('time_plan') === ((string)Event::PLAN_SINGLE)) {
            if ((string)$event->get('type') === ((string)Event::TYPE_TIME)) {
                $validator
                    ->requirePresence('usage_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                    ->allowEmptyString('usage_time', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('usage_time', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', array_keys($this->getUsageTimeValueOptions($event))],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            } elseif ((string)$event->get('type') === ((string)Event::TYPE_DAY)) {
                $validator
                    ->requirePresence('usage_day', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                    ->allowEmptyString('usage_day', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('usage_day', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', array_keys($this->getUsageDayValueOptions($event))],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            }
        } elseif ((string)$event->get('time_plan') === ((string)Event::PLAN_MULTIPLE)) {
            $multipleTimePlanType = $event->get('multiple_time_plan_type');
            if (((string)$multipleTimePlanType) === ((string)Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE)) {
                $eventPlanIdValidator = new KuchenValidator();
                $validator->addNested('plan_values', $eventPlanIdValidator);

                $eventPlanIdValidator
                    ->requirePresence('single', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                    ->allowEmptyString('single', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('single', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', array_keys($this->getEventPlansValueOptions($event))],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            } elseif (((string)$multipleTimePlanType) === ((string)Event::MULTIPLE_TIME_PLAN_TYPE_MULTI)) {
                $validator
                    ->requirePresence('plan_values', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                    ->allowEmptyString('plan_values', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('plan_values', [
                        'isArray' => [
                            'rule' => ['isArray'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'multiple' => [
                            'rule' => ['multiple', [
                                'in' => array_keys($this->getEventPlansValueOptions($event)),
                            ]],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            }
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        $columns = [
            Event::TYPE_TIME => 'usage_time',
            Event::TYPE_DAY => 'usage_day',
        ];

        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }
        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        return $reservation->get($columns[$event->get('type')]);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['reservations']['usage_time'] = $data;
        $result['reservations']['usage_day'] = $data;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.values');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        $timeMessages = Hash::get($errors, 'usage_time');
        $dayMessages = Hash::get($errors, 'usage_day');
        if (empty($timeMessages) && empty($dayMessages)) {
            return [];
        }
        $messages = array_merge((array)$timeMessages, (array)$dayMessages);

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'reserve_usetime';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $event = Hash::get($data, 'event');
        if (!isset($event)) {
            return null;
        }

        $column = [
            Event::TYPE_TIME => 'usage_time',
            Event::TYPE_DAY => 'usage_day',
        ];

        return Hash::get($data, 'reservation.' . $column[$event['type']]);
    }

    /**
     * 時間の選択肢を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return array 選択肢
     */
    public function getUsageTimeValueOptions(Event $event)
    {
        $includeValue = null;
        $event = $this->getConfig('event');
        $reservation = $this->getConfig('reservation');
        if (isset($reservation) && (string)$reservation->getOriginal('event_id') === (string)$event->get('id')) {
            $includeValue = $reservation->get('usage_time');
        }

        return $event->getUsageTimeValueOptions($this->isAdmin(), $includeValue);
    }

    /**
     * 日付の選択肢を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return array 選択肢
     */
    public function getUsageDayValueOptions(Event $event)
    {
        $includeValue = null;
        $event = $this->getConfig('event');
        $reservation = $this->getConfig('reservation');
        if (isset($reservation) && (string)$reservation->getOriginal('event_id') === (string)$event->get('id')) {
            $includeValue = $reservation->get('usage_day');
        }

        return $event->getUsageDayValueOptions($this->isAdmin(), $includeValue);
    }

    /**
     * 複数プランの選択肢を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return array 選択肢
     */
    public function getEventPlansValueOptions(Event $event)
    {
        $includeValues = null;
        $event = $this->getConfig('event');
        $reservation = $this->getConfig('reservation');
        if (isset($reservation) && (string)$reservation->getOriginal('event_id') === (string)$event->get('id')) {
            $includeValues = (array)$reservation->get('plan_values');
        }

        return $event->getEventPlansValueOptions($this->isAdmin(), $includeValues);
    }

    /**
     * 時間の選択肢の固定を判定
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return bool
     */
    public function isFixedUsageTimeValueOptions(Event $event)
    {
        return $event->isFixedUsageTimeValueOptions($this->isAdmin());
    }

    /**
     * 日付の選択肢の固定を判定
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return bool
     */
    public function isFixedUsageDayValueOptions(Event $event)
    {
        return $event->isFixedUsageDayValueOptions($this->isAdmin());
    }

    /**
     * 時間の初期値を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return int|null
     */
    public function getUsageTimeDefaultValue(Event $event)
    {
        if (!$this->isAdmin()) {
            return null;
        }

        return $event->get('usage_time_from');
    }

    /**
     * 日付の初期値を取得
     *
     * @param \App\Model\Entity\Event $event 予約枠
     * @return int|null
     */
    public function getUsageDayDefaultValue(Event $event)
    {
        if (!$this->isAdmin()) {
            return null;
        }

        return $event->get('usage_day_from');
    }

    /**
     * 時間の空の選択肢有無を判定
     *
     * @return bool
     */
    public function hasUsageTimeEmptyValue()
    {
        if (!$this->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * 日付の空の選択肢有無を判定
     *
     * @return bool
     */
    public function hasUsageDayEmptyValue()
    {
        if (!$this->isAdmin()) {
            return true;
        }

        return false;
    }
}
