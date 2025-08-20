<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\EventCalendar\EventTimetable;
use App\Model\EventCalendar\Traits\CalendarDetailTrait;
use App\Model\EventCalendar\Traits\TimetableTrait;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use App\Validation\CustomValidation;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Form\Schema;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 台帳詳細フォーム
 */
class CalendarDetailForm extends AppForm
{
    use CalendarDetailTrait;
    use TimetableTrait;

    public const PAGINATOR_LIMIT = 10;

    /**
     * @var \App\Model\Entity\Event|null
     */
    protected $eventEntity = null;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        $this->adminFlg = true;
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('event_id', 'string')
            ->addField('usage_timestamp_from', 'string')
            ->addField('usage_timestamp_to', 'string')
            ->addField('display_item', 'string')
            ->addField('user_id', 'string');

        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('event_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('event_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('event_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('usage_timestamp_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_timestamp_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('usage_timestamp_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => ['dateTime', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator
            ->requirePresence('usage_timestamp_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_timestamp_to', __(Message::ERROR_NOT_EMPTY), false)
            ->add('usage_timestamp_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => ['dateTime', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'isUnit' => [
                    'rule' => function ($value) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (!$validator->isValid('event_id') || !$validator->isValid('usage_timestamp_from')) {
                            return true;
                        }

                        $event = null;
                        try {
                            $event = $this->getEventEntity();
                        } catch (RecordNotFoundException $e) {
                            return false;
                        }

                        $usageTimestampFrom = new FrozenTime($this->getData('usage_timestamp_from'));
                        $usageTimestampTo = $event->getUsageTimestampTo($usageTimestampFrom);
                        if ($value !== $usageTimestampTo->format('Y/m/d H:i')) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('display_item', false)
            ->allowEmptyString('display_item')
            ->add('display_item', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('displayItem'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id')
            ->add('user_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $this->addPaginateValidation($validator, [
            'fieldValueOptions' => $this->getFieldValueOptions(),
        ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $displayItem = [];
        foreach ($formItemsTable->getFormItems() as $formItem) {
            if ($formItem->getInputTypeItem() instanceof CalendarOutputInterface) {
                $displayItem[$formItem->get('id')] = $formItem->get('name');
            }
        }

        $fieldValueOptions = [
            'displayItem' => $displayItem,
            'sort' => Hash::combine(['id', 'reservation_status_id', 'usage_timestamp_from', 'number'], '{*}'),
            'limit' => Hash::combine([static::PAGINATOR_LIMIT], '{*}'),
        ] + $this->buildPaginateFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'sort' => 'id',
            'direction' => 'desc',
            'limit' => static::PAGINATOR_LIMIT,
        ] + $this->buildPaginateDefaultFieldValues();

        return $defaultFieldValues;
    }

    /**
     * @inheritDoc
     */
    protected function getColorChipIds(): array
    {
        return [];
    }

    /**
     * 枠を取得
     *
     * @return \App\Model\EventCalendar\EventUnit
     */
    public function getEventUnit()
    {
        $displayItem = $this->getData('display_item');
        if (isset($displayItem)) {
            $displayItem = (int)$displayItem;
        } else {
            $displayItem = null;
        }
        $this->setCalendarDisplayItem($displayItem);

        $usageTimestampFrom = new FrozenTime($this->getData('usage_timestamp_from'));
        $usageTimestampTo = new FrozenTime($this->getData('usage_timestamp_to'));
        $eventTimetable = new EventTimetable($this->getEventEntity(), $usageTimestampFrom, $usageTimestampTo, true);
        $this->applyReservations([$eventTimetable], $usageTimestampFrom, $usageTimestampTo);
        $this->applyAdminCalendarData([$eventTimetable], $usageTimestampFrom, $usageTimestampTo);

        $eventUnit = $eventTimetable->getFirstUnit();
        if (!isset($eventUnit)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        return $eventUnit;
    }

    /**
     * 予約枠を取得
     *
     * @return \App\Model\Entity\Event
     */
    protected function getEventEntity()
    {
        if (!isset($this->eventEntity)) {
            /** @var \App\Model\Table\EventsTable $eventsTable */
            $eventsTable = $this->getTableLocator()->get('Events');

            $usageTimestampFrom = new FrozenTime($this->getData('usage_timestamp_from'));
            $this->eventEntity = $eventsTable->get($this->getData('event_id'), [
                'finder' => 'calendarPopup',
                'inputs' => [
                    'date_from' => $usageTimestampFrom->format('Y-m-d'),
                    'date_to' => $usageTimestampFrom->format('Y-m-d'),
                ],
            ]);
        }

        return $this->eventEntity;
    }
}
