<?php
declare(strict_types=1);

namespace App\Form\Admin\Events;

use App\Form\AppForm;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 予約枠検索フォーム
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
                'listCheck' => Configure::readOrFail('Master.common.listCheck'),
                'listCheckId' => Configure::readOrFail('Master.common.listCheckId'),
                'sort' => Hash::combine([
                    'id',
                    'type',
                    'name',
                    'label_id',
                    'public_flg',
                    'sort_no',
                    'date_from',
                    'time_from',
                    'organizer_id',
                    'smart_lock_device_key',
                ], '{*}'),
            ] + $this->buildEventSearchFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = $this->buildEventSearchDefaultFieldValues();

        return $defaultFieldValues;
    }
}
