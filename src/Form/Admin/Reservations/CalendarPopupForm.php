<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Common\Reservations\CalendarPopupForm as CommonCalendarPopupForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * カレンダーポップアップフォーム
 */
class CalendarPopupForm extends CommonCalendarPopupForm
{
    use CalendarFormTrait;

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

        $this->addAdminCalendarSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        parent::validationDefault($validator);

        $this->addAdminCalendarValidator($validator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = parent::buildFieldValueOptions();

        $fieldValueOptions += $this->buildAdminCalendarFieldValueOptions();

        return $fieldValueOptions;
    }
}
