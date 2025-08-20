<?php
declare(strict_types=1);

namespace App\Form\Admin\AutoReplyMails;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 自動返信メール検索フォーム
 */
class SearchForm extends AppForm
{
    public const FROM_NAME_MAX = 1000;
    public const FROM_MAIL_MAX = 256;
    public const CONTENTS_MAX = 1000;
    public const SUBJECT_MAX = 1000;

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $userAuthorityLists = $userAuthoritiesTable->getSelectList();

        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplyMailsTable */
        $autoReplyMailsTable = $this->getTableLocator()->get('AutoReplyMails');

        $this->setFieldValueOptions([
            'type' => $autoReplyMailsTable->getTypeFieldValueOptions(),
            'contentType' => Configure::readOrFail('Master.common.mailFormatName'),
            'userAuthorityId' => $userAuthorityLists,
            'sort' => Hash::combine([
                'id',
                'type',
                'user_authority_id',
                'label_id',
                'from_mail_name',
                'from_mail',
                'subject',
                'content_type',
            ], '{*}'),
            'direction' => Configure::read('Setting.pagination.direction'),
            'limit' => $this->generatePaginateLimit(Configure::read('Setting.pagination.limit.config')),
        ]);

        $this->setDefaultFieldValues([
            'label_id' => $this->commonData()->getAdminLoginLabel(),
            'sort' => 'type',
            'direction' => 'asc',
            'limit' => Configure::read('Setting.pagination.limit.default'),
            'page' => '1',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('type', 'integer')
            ->addField('user_authority_id', 'integer')
            ->addField('label_id', 'integer')
            ->addField('from_mail', 'string')
            ->addField('from_mail_name', 'string')
            ->addField('subject', 'string')
            ->addField('contents', 'string')
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $validator
            ->requirePresence('type', false)
            ->allowEmptyString('type')
            ->add('type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('type')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

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
                    'rule' => [
                        'multiple',
                        [
                            'in' => array_keys($this->getFieldValueOptions('userAuthorityId')),
                        ],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('label_id', false)
            // 担当カテゴリがある管理者の場合は入力必須にする(マスター管理者を除く)
            ->allowEmptyString('label_id', __(Message::ERROR_INVALID_VALUE), function () {
                /** @var \App\Model\Entity\Admin $loginData */
                $loginData = $this->commonData()->getAdminLoginData();
                if ($loginData->isMasterAdmin()) {
                    return true;
                }

                return $this->commonData()->getAdminLoginLabel() === null;
            });
        $validator = $labelsTable->addValidateLabelId($validator);
        $validator = $labelsTable->addValidateLabelIdAdminUsable($validator);

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
            ->requirePresence('from_mail', false)
            ->allowEmptyString('from_mail')
            ->add('from_mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::FROM_MAIL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::FROM_MAIL_MAX),
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
            ->requirePresence('contents', false)
            ->allowEmptyString('contents')
            ->add('contents', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CONTENTS_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CONTENTS_MAX),
                ],
            ]);

        $this->addPaginateValidation($validator, [
            'fieldValueOptions' => $this->getFieldValueOptions(),
        ]);

        return $validator;
    }
}
