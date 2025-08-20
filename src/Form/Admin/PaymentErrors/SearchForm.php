<?php
declare(strict_types=1);

namespace App\Form\Admin\PaymentErrors;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Table\PaymentErrorsTable;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 決済エラー一覧検索フォーム
 */
class SearchForm extends AppForm
{
    public const TITLE_MAX = 100;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('ip_address', 'string')
            ->addField('last_error_timestamp_from', 'datetime')
            ->addField('last_error_timestamp_to', 'datetime')
            ->addField('lock_timestamp', 'integer')
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
            ->requirePresence('ip_address', false)
            ->allowEmptyString('ip_address')
            ->add('ip_address', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', PaymentErrorsTable::IP_ADDRESS_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, [PaymentErrorsTable::IP_ADDRESS_MAX]),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
            ]);

        $validator
            ->requirePresence('last_error_timestamp_from', false)
            ->allowEmptyString('last_error_timestamp_from')
            ->add('last_error_timestamp_from', [
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
            ->requirePresence('last_error_timestamp_to', false)
            ->allowEmptyString('last_error_timestamp_to')
            ->add('last_error_timestamp_to', [
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
                    'rule' => [
                        'compareDateTimeFields',
                        'last_error_timestamp_from',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function ($context) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        return $validator->isValid('last_error_timestamp_from');
                    },
                ],
            ]);

        $validator
            ->requirePresence('lock_timestamp', false)
            ->allowEmptyArray('lock_timestamp')
            ->add('lock_timestamp', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('status')),
                    ]],
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
        $fieldValueOptions = [
            'sort' => Hash::combine(['id', 'ip_address', 'last_error_timestamp', 'all_error_count'], '{*}'),
            'direction' => Configure::readOrFail('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::readOrFail('Setting.pagination.limit.config')),
            'status' => Configure::readOrFail('Master.paymentErrors.status'),
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
            'direction' => 'desc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }
}
