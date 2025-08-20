<?php
declare(strict_types=1);

namespace App\Form\Admin\AdminOperationalLogs;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 操作ログ検索フォーム
 */
class SearchForm extends AppForm
{
    public const TEXT_MAX = 1000;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('authority', 'array')
            ->addField('login_id', 'string')
            ->addField('operated_function', 'array')
            ->addField('created_from', 'datetime')
            ->addField('created_to', 'datetime')
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
            ->requirePresence('operated_function', false)
            ->allowEmptyArray('operated_function')
            ->add('operated_function', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('function')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('created_from', false)
            ->allowEmptyDateTime('created_from')
            ->add('created_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator
            ->requirePresence('created_to', false)
            ->allowEmptyDateTime('created_to')
            ->add('created_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'created_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'last' => true,
                    'on' => function ($context) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (
                            $validator->isValid('created_from')
                            && Validation::notBlank(Hash::get($context['data'], 'created_from'))
                        ) {
                            return true;
                        }

                        return false;
                    },
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
                    'rule' => ['maxLength', static::TEXT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TEXT_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', Configure::readOrFail('Setting.auth.admin.loginId.character')],
                    'last' => true,
                    'message' => __(Message::ERROR_LOGIN_ID_CHARACTER),
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
            'function' => Configure::readOrFail('Master.operation.function'),
            'sort' => Hash::combine(['created', 'login_id', 'operated_function', 'operated_type'], '{*}'),
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
            'sort' => 'created',
            'direction' => 'desc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
