<?php
declare(strict_types=1);

namespace App\Form\Admin\Options;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Table\OptionsTable;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * オプション検索フォーム
 */
class SearchForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('name', 'string')
            ->addField('date', 'date')
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
            ->requirePresence('date', false)
            ->allowEmptyDate('date')
            ->add('date', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => [
                        'date',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

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
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('authority')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $validator
            ->requirePresence('name', false)
            ->allowEmptyString('name')
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', OptionsTable::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, OptionsTable::NAME_MAX),
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
            'sort' => Hash::combine(['id', 'name', 'usage_timestamp_from'], '{*}'),
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
