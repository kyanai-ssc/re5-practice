<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Locale\Message;
use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Validation\CustomValidation;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * カレンダーポップアップフォーム
 */
abstract class CalendarPopupForm extends AppForm
{
    use CalendarFormTrait;
    use CommonFormTrait;

    /**
     * @var \App\Model\EventCalendar\AbstractCalendarPopup
     */
    protected $calendarPopup = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('id', 'string')
            ->addField('date', 'string');

        $this->addCalendarSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('id', __(Message::ERROR_NOT_EMPTY), false);
        if (!$this->calendarPopup->isMultipleEventType()) {
            $validator->add('id', [
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
        } else {
            $validator->add('id', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => function ($values) {
                        foreach ($values as $value) {
                            if (!CustomValidation::integer($value, CustomValidation::BIGINT_MAX)) {
                                return false;
                            }
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);
        }

        $validator
            ->requirePresence('date', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('date', __(Message::ERROR_NOT_EMPTY), false)
            ->add('date', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $this->addCalendarValidator($validator);

        return $validator;
    }

    /**
     * カレンダーポップアップを取得
     *
     * @return \App\Model\EventCalendar\AbstractCalendarPopup
     */
    public function getCalendarPopup()
    {
        return $this->calendarPopup;
    }

    /**
     * カレンダーポップアップを設定
     *
     * @param \App\Model\EventCalendar\AbstractCalendarPopup $calendarPopup カレンダーポップアップ
     * @return void
     */
    public function setCalendarPopup(AbstractCalendarPopup $calendarPopup)
    {
        $this->calendarPopup = $calendarPopup;
    }
}
