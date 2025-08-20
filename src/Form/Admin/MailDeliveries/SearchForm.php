<?php
declare(strict_types=1);

namespace App\Form\Admin\MailDeliveries;

use App\Form\Admin\Users\SearchFormTrait;
use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\AdminSearchItem;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * メール配信履歴検索フォーム
 */
class SearchForm extends AppForm
{
    use SearchFormTrait;

    public const MAIL_MAX = 1000;
    public const FROM_NAME_MAX = 100;
    public const SUBJECT_MAX = 100;
    public const CONTENTS_MAX = 50000;

    /**
     * @var array|null
     */
    protected $searchItems = null;

    /**
     * @var array|null
     */
    protected $listItems = null;

    /**
     * @var array|null
     */
    protected $mailDeliverySchema = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('send_type', 'integer')
            ->addField('send_date_from', 'date')
            ->addField('send_time_from', 'time')
            ->addField('send_date_to', 'date')
            ->addField('send_time_to', 'time')
            ->addField('from_mail', 'string')
            ->addField('reply_to', 'string')
            ->addField('from_mail_name', 'string')
            ->addField('subject', 'string')
            ->addField('send_status', 'integer')
            ->addField('sort', 'string')
            ->addField('direction', 'string')
            ->addField('limit', 'integer')
            ->addField('page', 'integer');

        $this->mailDeliverySchema = $schema->fields();

        $schema = $this->buildUserSearchSchema($schema, $this->getSearchItems());
        $this->addPaginateSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $userFieldValueOptions = $this->buildUserSearchFieldValueOptions();

        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->getTableLocator()->get('MailDeliveries');

        $fieldValueOptions = [
                'sendType' => Configure::readOrFail('Master.mailDelivery.sendType'),
                'sendTime' => $mailDeliveriesTable->get30minSeparatedTime(),
                'sendStatus' => Configure::readOrFail('Master.mailDelivery.status'),
                'sort' => Hash::combine([
                    'send_type',
                    'send_timestamp',
                    'reply_to',
                    'from_mail',
                    'from_mail_name',
                    'subject',
                    'send_status',
                ], '{*}'),
                'direction' => Configure::readOrFail('Setting.pagination.direction'),
                'limit' => $this->generatePaginateLimit(Configure::readOrFail('Setting.pagination.limit.config')),
            ] + $userFieldValueOptions;

        return $fieldValueOptions;
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

        $validator
            ->requirePresence('send_type', false)
            ->allowEmptyArray('send_type')
            ->add('send_type', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('sendType')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('send_status', false)
            ->allowEmptyArray('send_status')
            ->add('send_status', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('sendStatus')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('from_mail', false)
            ->allowEmptyString('from_mail')
            ->add('from_mail', [
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
            ->requirePresence('reply_to', false)
            ->allowEmptyString('reply_to')
            ->add('reply_to', [
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
            ->requirePresence('from_mail_name', false)
            ->allowEmptyString('from_mail_name')
            ->add('from_mail_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::FROM_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::FROM_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('subject', false)
            ->allowEmptyString('subject')
            ->add('subject', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SUBJECT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SUBJECT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('send_date_from', false)
            ->allowEmptyDate('send_date_from')
            ->add('send_date_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence('send_time_from', false)
            ->allowEmptyTime('send_time_from')
            ->add('send_time_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('sendTime'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('send_date_to', false)
            ->allowEmptyDate('send_date_to')
            ->add('send_date_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'send_date_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        return $validator->isValid('send_date_from');
                    },
                ],
            ]);

        $validator
            ->requirePresence('send_time_to', false)
            ->allowEmptyTime('send_time_to')
            ->add('send_time_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('sendTime'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'send_time_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function ($context) use ($validator) {
                        //日の入力が一致している場合時間を比較する
                        return $validator instanceof \Kuchen\Validation\Validation\Validator
                            && !$validator->hasError('send_date_from')
                            && !$validator->hasError('send_date_to')
                            && $context['data']['send_date_from'] === $context['data']['send_date_to'];
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'sort' => 'send_timestamp',
            'direction' => 'desc',
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

    /**
     * 検索項目の表示設定を取得
     *
     * @return array 表示設定
     */
    public function getSearchItems()
    {
        if (!isset($this->searchItems)) {
            /** @var \App\Model\Table\AdminSearchItemsTable $adminSearchItemsTable */
            $adminSearchItemsTable = $this->getTableLocator()->get('AdminSearchItems');

            $searchItems = $adminSearchItemsTable->find('formItem', [
                'inputs' => ['type' => AdminSearchItem::TYPE_MAIL_DELIVERIES],
            ])->first();
            if (!is_array($searchItems)) {
                throw new CakeException();
            }
            $this->searchItems = $searchItems;
        }

        return $this->searchItems;
    }

    /**
     * 検索項目の表示設定を取得
     *
     * @return array 表示設定
     */
    public function getListItems()
    {
        if (!isset($this->listItems)) {
            /** @var \App\Model\Table\AdminListItemsTable $adminListItemsTable */
            $adminListItemsTable = $this->getTableLocator()->get('AdminListItems');

            $listItems = $adminListItemsTable->find('formItem', [
                'inputs' => ['type' => AdminSearchItem::TYPE_MAIL_DELIVERIES],
            ])->first();
            if (!is_array($listItems)) {
                throw new CakeException();
            }
            $this->listItems = $listItems;
        }

        return $this->listItems;
    }

    /**
     * メール配信履歴の検索スキーマを返却
     *
     * @return array|null
     */
    public function getMailDeliverySchema()
    {
        return $this->mailDeliverySchema;
    }
}
