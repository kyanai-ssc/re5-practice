<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * CalendarForm trait.
 */
trait CalendarFormTrait
{
    /**
     * カレンダーのスキーマを追加
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return void
     */
    protected function addCalendarSchema(Schema $schema)
    {
        $schema
            ->addField('edit_reservation_id', 'string')
            ->addField('select_usage_timestamp_from', 'string');
    }

    /**
     * カレンダーのバリデータを追加
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return void
     */
    protected function addCalendarValidator(Validator $validator)
    {
        $validator
            ->requirePresence('edit_reservation_id', false)
            ->allowEmptyString('edit_reservation_id')
            ->add('edit_reservation_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        $validator
            ->requirePresence('select_usage_timestamp_from', false)
            ->allowEmptyString('select_usage_timestamp_from')
            ->add('select_usage_timestamp_from', [
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
    }
}
