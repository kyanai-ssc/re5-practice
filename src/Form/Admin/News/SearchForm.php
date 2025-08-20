<?php
declare(strict_types=1);

namespace App\Form\Admin\News;

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
 * お知らせ検索フォーム
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
            ->addField('title', 'integer')
            ->addField('public_from', 'datetime')
            ->addField('public_to', 'datetime')
            ->addField('sort_no', 'integer')
            ->addField('user_authority_id', 'integer')
            ->addField('label_id', 'string')
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
        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');

        $validator
            ->requirePresence('label_id', false)
            ->allowEmptyString('label_id');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('user_authority_id', false)
            ->allowEmptyArray('user_authority_id')
            ->add('user_authority_id', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('userAuthorityId')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('title', false)
            ->allowEmptyString('title')
            ->add('title', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', self::TITLE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TITLE_MAX),
                ],
            ]);

        $validator->requirePresence('public_from', false)
            ->allowEmptyDateTime('public_from')
            ->add('public_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator->requirePresence('public_to', false)
            ->allowEmptyDateTime('public_to')
            ->add('public_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'public_from', Validation::COMPARE_GREATER],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        return $validator->isValid('public_from');
                    },
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
            'sort' => Hash::combine(['title', 'sort_no', 'label_id', 'public_from'], '{*}'),
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
            'label_id' => $this->commonData()->getAdminLoginLabel(),
            'sort' => 'sort_no',
            'direction' => 'asc',
            'limit' => Configure::readOrFail('Setting.pagination.limit.default'),
            'page' => '1',
        ];

        return $defaultFieldValues;
    }

    /**
     * フィールドの値リストへ追加
     *
     * @param array $add 追加する値
     * @return void
     */
    public function addFieldValueOptions(array $add)
    {
        $fieldValueOptions = $this->getFieldValueOptions();
        $fieldValueOptions += $add;

        $this->setFieldValueOptions($fieldValueOptions);
    }
}
