<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Admin\Events\SearchFormTrait as EventSearchFormTrait;
use App\Form\Common\Reservations\CalendarForm as CommonCalendarForm;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * カレンダーフォーム
 */
class CalendarForm extends CommonCalendarForm
{
    use CalendarFormTrait;
    use EventSearchFormTrait;

    /**
     * @var bool
     */
    protected $adminFlg = true;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        $schema
            ->addField('name', 'string')
            ->addField('public_from', 'string')
            ->addField('public_to', 'string')
            ->addField('public_flg', 'string')
            ->addField('schedule_date_from', 'string')
            ->addField('schedule_date_to', 'string')
            ->addField('type', 'string');

        $this->addAdminCalendarSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        parent::validationDefault($validator);

        $eventValidator = $this->buildEventSearchValidator(new KuchenValidator(), [
            'textMax' => EventsTable::NAME_MAX,
            'pageValidator' => false,
        ]);

        $validator
            ->requirePresence('name', false)
            ->allowEmptyString('name')
            ->offsetSet('name', $eventValidator->field('name'));

        $validator
            ->requirePresence('public_from', false)
            ->allowEmptyString('public_from')
            ->offsetSet('public_from', $eventValidator->field('public_from'));

        $validator
            ->requirePresence('public_to', false)
            ->allowEmptyString('public_to')
            ->offsetSet('public_to', $eventValidator->field('public_to'));

        $validator
            ->requirePresence('public_flg', false)
            ->allowEmptyString('public_flg')
            ->offsetSet('public_flg', $eventValidator->field('public_flg'));

        $validator
            ->requirePresence('type', false)
            ->allowEmptyArray('type')
            ->offsetSet('type', $eventValidator->field('type'));

        $this->addAdminCalendarValidator($validator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = parent::buildFieldValueOptions();

        $fieldValueOptions = [
                'type' => Configure::readOrFail('Master.event.type'),
                'publicFlg' => Configure::readOrFail('Master.event.publicFlg'),
            ] + $this->buildAdminCalendarFieldValueOptions() + $fieldValueOptions;

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function getParameters()
    {
        $data = parent::getParameters();
        $data['user_id'] = $this->getData('user_id');

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = parent::buildDefaultFieldValues();
        $defaultFieldValues['label_id'] = $this->commonData()->getAdminLoginLabel();

        return $defaultFieldValues;
    }

    /**
     * 会員IDチェック
     *
     * @param mixed $userId 会員ID
     * @param bool $selectCalendar カレンダー選択
     * @return bool
     */
    public function validateUserId($userId, bool $selectCalendar = false)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        if (!isset($userId) || $userId === '') {
            return true;
        }

        if (!$usersTable->validatePrimaryKey($userId)) {
            return false;
        }
        try {
            /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
            $usersTable->get($userId, [
                'finder' => 'calendar',
                'selectCalendar' => $selectCalendar,
            ]);
        } catch (RecordNotFoundException $e) {
            return false;
        }

        return true;
    }
}
