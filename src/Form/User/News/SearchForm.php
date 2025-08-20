<?php
declare(strict_types=1);

namespace App\Form\User\News;

use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * お知らせフォーム
 */
class SearchForm extends AppForm
{
    public const PAGE_LIMIT = 10;

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

        return $fieldValueOptions;
    }
}
