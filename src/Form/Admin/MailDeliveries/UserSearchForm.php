<?php
declare(strict_types=1);

namespace App\Form\Admin\MailDeliveries;

use App\Form\Admin\Users\SearchFormTrait;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * メール配信履歴 会員検索フォーム
 */
class UserSearchForm extends SearchForm
{
    use SearchFormTrait;

    /**
     * @var array|null
     */
    protected $searchItems = null;

    /**
     * @var array|null
     */
    protected $listItems = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildUserSearchSchema($schema, $this->getSearchItems());

        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildUserSearchValidator($validator);

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
        $userFieldValueOptions = $this->buildUserSearchFieldValueOptions();

        $fieldValueOptions = [
            'sort' => $userFieldValueOptions['sort'],
            'listCheck' => Configure::readOrFail('Master.common.listCheck'),
            'listCheckId' => Configure::readOrFail('Master.common.listCheckId'),
        ];

        $fieldValueOptions += $this->buildPaginateFieldValueOptions() + $userFieldValueOptions;

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = $this->buildPaginateDefaultFieldValues() + $this->buildUserSearchDefaultFieldValues();
        $defaultFieldValues['sort'] = 'Users.id';

        return $defaultFieldValues;
    }
}
