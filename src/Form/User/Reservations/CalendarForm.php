<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\Common\Reservations\CalendarForm as CommonCalendarForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * カレンダーフォーム
 */
class CalendarForm extends CommonCalendarForm
{
    /**
     * 予約枠名：MAX
     */
    public const EVENT_NAME_MAX = 320;

    /**
     * @var bool
     */
    protected $adminFlg = false;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        $schema
            ->addField('s', 'string')
            ->addField('event_name', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        parent::validationDefault($validator);

        $validator
            ->requirePresence('s', false)
            ->allowEmptyArray('s')
            ->add('s', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('event_name', false)
            ->allowEmptyString('event_name')
            ->add('event_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::EVENT_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::EVENT_NAME_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $fieldValueOptions = parent::buildFieldValueOptions();

        $calendarType = array_fill_keys($this->commonData()->getUserAuthority()->get('calendar_type'), true);

        $fieldValueOptions = [
            'calendarType' => array_intersect_key($fieldValueOptions['calendarType'], $calendarType),
        ] + $fieldValueOptions;

        $siteSetting = $siteSettingsTable->getData();
        if ($siteSetting->get('calendar_search_event_name_flg')) {
            $fieldValueOptions += [
                'eventNameList' => $eventsTable->getEventNameForPublic(true),
            ];
        }

        return $fieldValueOptions;
    }
}
