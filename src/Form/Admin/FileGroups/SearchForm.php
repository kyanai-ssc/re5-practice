<?php
declare(strict_types=1);

namespace App\Form\Admin\FileGroups;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ファイル検索フォーム
 */
class SearchForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('file_group_id', 'integer')
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
        $validator
            ->requirePresence('file_group_id', false)
            ->allowEmptyString('file_group_id')
            ->add('file_group_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('fileGroups'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

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
        $fileGroupsTable = $this->getTableLocator()->get('FileGroups');

        $fileGroupList = $fileGroupsTable->find('list', [
            'keyField' => 'id',
            'valueField' => 'name',
        ])->select([
            'FileGroups.id',
            'FileGroups.name',
        ])->toArray();

        $fieldValueOptions = [
            'fileGroups' => $fileGroupList,
            'authority' => Configure::readOrFail('Master.admin.authority'),
            'sort' => Hash::combine(['file_group_id', 'file_group_name', 'id'], '{*}'),
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
            'sort' => 'file_group_id',
            'direction' => 'asc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
