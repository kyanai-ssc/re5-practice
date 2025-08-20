<?php
declare(strict_types=1);

namespace App\Form\User\WaitingCancellations;

use App\Form\AppForm;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * キャンセル待ち通知一覧フォーム
 */
class SearchForm extends AppForm
{
    public const PAGE_LIMIT = 20;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('page', 'integer');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
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
        $fieldValueOptions = $this->buildPaginateFieldValueOptions();

        $paginationSetting = Configure::readOrFail('Setting.pagination.limit.config');
        $paginationSetting['limit']['config']['start'] = static::PAGE_LIMIT;
        $paginationSetting['limit']['config']['default'] = static::PAGE_LIMIT;
        $fieldValueOptions['limit'] = $this->generatePaginateLimit($paginationSetting);

        return $fieldValueOptions;
    }
}
