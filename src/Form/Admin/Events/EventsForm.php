<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\Event as EventEntity;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 予約枠フォーム
 */
class EventsForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('id', 'integer')
            ->addField('type', 'integer')
            ->addField('time_plan', 'integer')
            ->addField('time_from', 'time')
            ->addField('time_to', 'time')
            ->addField('date_from', 'time')
            ->addField('date_to', 'time')
            ->addField('usage_unit_time', 'integer')
            ->addField('event_unit_time', 'integer')
            ->addField('usage_time_from', 'integer')
            ->addField('usage_time_to', 'integer')
            ->addField('usage_unit_day', 'integer')
            ->addField('usage_day_from', 'integer')
            ->addField('usage_day_to', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $eventsTable = $this->getTableLocator()->get('Events');
        $eventValidator = $eventsTable->getValidator();

        $validator
            ->offsetSet('time_plan', $eventValidator->field('time_plan'));

        $validator
            ->requirePresence('time_plan', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('time_plan', __(Message::ERROR_NOT_EMPTY_SELECT), false);

        $validator
            ->offsetSet('time_from', $eventValidator->field('time_from'));

        $validator
            ->requirePresence('time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('time_from', __(Message::ERROR_NOT_EMPTY), false);

        $validator
            ->offsetSet('time_to', $eventValidator->field('time_to'));

        $validator
            ->requirePresence('time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('time_to', __(Message::ERROR_NOT_EMPTY), false);

        $validator
            ->offsetSet('event_unit_time', $eventValidator->field('event_unit_time'));

        $validator
            ->requirePresence('event_unit_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('event_unit_time', __(Message::ERROR_NOT_EMPTY), false);

        $validator
            ->offsetSet('usage_unit_time', $eventValidator->field('usage_unit_time'));

        $validator
            ->requirePresence('usage_unit_time', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_unit_time', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)EventEntity::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            });

        $validator
            ->offsetSet('usage_time_from', $eventValidator->field('usage_time_from'));

        $validator
            ->requirePresence('usage_time_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_time_from', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)EventEntity::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            });

        $validator
            ->offsetSet('usage_time_to', $eventValidator->field('usage_time_to'));

        $validator
            ->requirePresence('usage_time_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('usage_time_to', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                if (
                    $validator->isValid('time_plan')
                    && (string)Hash::get($context['data'], 'time_plan') === (string)EventEntity::PLAN_MULTIPLE
                ) {
                    return true;
                }

                return false;
            });

        return $validator;
    }

    /**
     * プレビューデータを置換
     *
     * @param array $eventInputs 入力値
     * @return array|string|null
     */
    public function replacePreviewData(array $eventInputs)
    {
        $eventInputs = [
            'id' => -1,
            'name' => 'プレビュー',
            'sort_no' => '1',
            'public_from' => $this->commonData()->getNowDateTime()->format('Y/m/d') . ' 00:00',
            'public_to' => $this->commonData()->getNowDateTime()->addDays(1)->format('Y/m/d') . ' 24:00',
            'public_flg' => '1',
            'type' => '1',
            'reservation_status_id' => '1',
            'date_from' => $this->commonData()->getNowDateTime()->format('Y/m/d'),
            'date_to' => $this->commonData()->getNowDateTime()->addDays(1)->format('Y/m/d'),
            'time_from' => $eventInputs['time_from'] ?? null,
            'time_to' => $eventInputs['time_to'] ?? null,
            'event_unit_time' => $eventInputs['event_unit_time'] ?? null,
            'usage_unit_time' => $eventInputs['usage_unit_time'] ?? null,
            'usage_time_from' => $eventInputs['usage_time_from'] ?? null,
            'usage_time_to' => $eventInputs['usage_time_to'] ?? null,
            'usage_unit_day' => '1',
            'usage_day_from' => '1',
            'usage_day_to' => '1',
            'time_plan' => $eventInputs['time_plan'] ?? null,
            'multiple_time_plan_type' => '',
            'interval_time' => '',
            'interval_day' => '',
            'stock' => '100',
            'usage_time_notation' => '1',
            'charge' => '0',
            'stock_unit' => '人',
            'stock_range_from' => '1',
            'stock_range_to' => '100',
            'reservation_limit_future' => '5',
            'reservation_limit_month' => '5',
            'reservation_limit_day' => '5',
            'reservation_limit_all' => '4',
            'stock_display_type' => '1',
            'reception_period_number' => '10',
            'reception_period_time' => '00:00',
            'registration_deadline_number' => '0',
            'registration_deadline_type' => '1',
            'registration_deadline_time' => null,
            'editing_deadline_number' => '2',
            'editing_deadline_type' => '2',
            'editing_deadline_time' => '02:00',
            'cancellation_deadline_number' => '3',
            'cancellation_deadline_type' => '2',
            'cancellation_deadline_time' => '00:00',
            'waiting_cancellation_flg' => '0',
            'duplication_check_flg' => '0',
            'background_color_type' => '1',
            'color_chip_id' => '',
            'background_color_replace_front' => [
                (int)0 => '1',
                (int)1 => '2',
                (int)2 => '4',
            ],
            'background_color_replace_admin' => [
                (int)0 => '1',
                (int)1 => '2',
                (int)2 => '4',
            ],
            'format_type_display' => [
                (int)0 => '1',
                (int)1 => '2',
                (int)2 => '3',
                (int)3 => '4',
                (int)4 => '5',
                (int)5 => '6',
                (int)6 => '7',
                (int)7 => '8',
            ],
            'form_pattern_id' => '1',
            'event_stock_settings' => [],
        ];

        return $eventInputs;
    }
}
