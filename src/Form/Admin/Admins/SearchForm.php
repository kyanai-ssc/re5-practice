<?php
declare(strict_types=1);

namespace App\Form\Admin\Admins;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 管理者検索フォーム
 */
class SearchForm extends AppForm
{
    public const LOGIN_ID_MAX = 1000;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('label_id', 'integer')
            ->addField('authority', 'integer')
            ->addField('login_id', 'string')
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
            ->requirePresence('label_id', false)
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('authority', false)
            ->allowEmptyArray('authority')
            ->add('authority', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('authority')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('login_id', false)
            ->allowEmptyString('login_id')
            ->add('login_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::LOGIN_ID_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::LOGIN_ID_MAX),
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
        $fieldValueOptions = [
            'authority' => Configure::readOrFail('Master.admin.authority'),
            'sort' => Hash::combine(['label_id', 'authority', 'admin_authority_id', 'login_id'], '{*}'),
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
            'sort' => 'label_id',
            'direction' => 'asc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
