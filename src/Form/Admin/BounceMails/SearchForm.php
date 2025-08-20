<?php
declare(strict_types=1);

namespace App\Form\Admin\BounceMails;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 不達メール検索フォーム
 */
class SearchForm extends AppForm
{
    public const MAIL_MAX = 1000;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('send_exclude_flg', 'array')
            ->addField('mail', 'integer')
            ->addField('user_id', 'string')
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
            ->requirePresence('send_exclude_flg', false)
            ->allowEmptyString('send_exclude_flg')
            ->add('send_exclude_flg', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('sendExclude')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('mail', false)
            ->allowEmptyString('mail')
            ->add('mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                ],
            ]);

        $validator
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id')
            ->add('user_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', CustomValidation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
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
            'listCheck' => Configure::readOrFail('Master.common.listCheck'),
            'listCheckId' => Configure::readOrFail('Master.common.listCheckId'),
            'sendExclude' => Configure::readOrFail('Master.bounceMail.sendExclude'),
            'sort' => Hash::combine([
                'id',
                'send_exclude_flg',
                'mail',
                'remaining_number',
                'total_number',
            ], '{*}'),
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
