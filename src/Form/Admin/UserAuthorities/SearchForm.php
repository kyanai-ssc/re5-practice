<?php
declare(strict_types=1);

namespace App\Form\Admin\UserAuthorities;

use App\Form\AppForm;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 会員権限検索フォーム
 */
class SearchForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('form_pattern_id', 'integer')
            ->addField('name', 'string')
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
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
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');
        $fieldValueOptions = $userAuthoritiesTable->getFieldValueOptions();

        $fieldValueOptions += [
            'authority' => Configure::readOrFail('Master.admin.authority'),
            'sort' => Hash::combine(['id', 'name', 'form_pattern_id'], '{*}'),
            'direction' => Configure::readOrFail('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::readOrFail('Setting.pagination.limit.config')),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'sort' => 'id',
            'direction' => 'asc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
