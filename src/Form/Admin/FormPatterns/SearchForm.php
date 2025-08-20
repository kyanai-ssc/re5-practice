<?php
declare(strict_types=1);

namespace App\Form\Admin\FormPatterns;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 表示パターン検索フォーム
 */
class SearchForm extends AppForm
{
    public const NAME_MAX = 100;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('name', 'integer')
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
            ->requirePresence('name', false)
            ->allowEmptyString('name')
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
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
            'sort' => Hash::combine(['id', 'name', 'remark'], '{*}'),
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
