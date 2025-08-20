<?php
declare(strict_types=1);

namespace App\Form\Admin\EventHolidays;

use App\Form\Admin\Events\SearchFormTrait;
use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 予約枠休日検索フォーム
 */
class SearchForm extends AppForm
{
    use SearchFormTrait;

    public const TEXT_MAX = 320;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildEventSearchSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildEventSearchValidator($validator, [
            'textMax' => static::TEXT_MAX,
        ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
                'sort' => Hash::combine(['event_id', 'date_from', 'time_from'], '{*}'),
            ] + $this->buildEventSearchFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = $this->buildEventSearchDefaultFieldValues();

        $defaultFieldValues['sort'] = 'event_id';
        $defaultFieldValues['direction'] = 'desc';

        return $defaultFieldValues;
    }
}
