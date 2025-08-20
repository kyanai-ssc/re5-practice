<?php
declare(strict_types=1);

namespace App\Form\Admin\AutoReplyMails;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * テストフォーム
 */
class TestMail extends AppForm
{
    public const MAIL_MAX = 254;

    /**
     * エラー出力時の項目名
     *
     * @var array
     */
    protected $formName = [
        'test_mail' => 'テストメール配信先',
        'from_mail_name' => 'FROM名',
        'from_mail' => 'FROMアドレス',
        'reply_to' => '返信先アドレス',
        'subject' => '件名',
        'header' => '挨拶文',
        'contents' => '本文',
        'footer' => '署名',
        'header_admin' => '管理者用挨拶文',
        'contents_admin' => '管理者用本文',
        'footer_admin' => '管理者用署名',
        'content_type' => 'メール配信形式',
        'admin_operation_mail_flg' => '管理者操作時のメール',
        'type' => 'タイプ',
    ];

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('test_mail', 'string')
            ->addField('from_mail_name', 'string')
            ->addField('from_mail', 'string')
            ->addField('reply_to', 'string')
            ->addField('subject', 'string')
            ->addField('header', 'string')
            ->addField('contents', 'string')
            ->addField('footer', 'string')
            ->addField('header_admin', 'string')
            ->addField('contents_admin', 'string')
            ->addField('footer_admin', 'string')
            ->addField('content_type', 'int')
            ->addField('admin_operation_mail_flg', 'int')
            ->addField('type', 'int');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('test_mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('test_mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('test_mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
            ]);

        /** @var \App\Model\Table\AutoReplyMailsTable $autoReplayMailTable */
        $autoReplayMailTable = $this->getTableLocator()->get('AutoReplyMails');
        $defaultValidator = $autoReplayMailTable->getValidator('default');

        $validator->field('content_type', $defaultValidator->field('content_type'));
        $validator->requirePresence('content_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('content_type', __(Message::ERROR_NOT_EMPTY_SELECT), false);

        $validator->field('from_mail_name', $defaultValidator->field('from_mail_name'));
        $validator->requirePresence('from_mail_name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail_name', __(Message::ERROR_NOT_EMPTY), false);

        $validator->field('from_mail', $defaultValidator->field('from_mail'));
        $validator->requirePresence('from_mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail', __(Message::ERROR_NOT_EMPTY), false);

        $validator->field('reply_to', $defaultValidator->field('reply_to'));
        $validator->requirePresence('reply_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reply_to', __(Message::ERROR_NOT_EMPTY), true);

        $validator->field('subject', $defaultValidator->field('subject'));
        $validator->requirePresence('subject', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('subject', __(Message::ERROR_NOT_EMPTY), false);

        $validator->field('header', $defaultValidator->field('header'));
        $validator->requirePresence('header', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('header', __(Message::ERROR_NOT_EMPTY), true);

        $validator->field('contents', $defaultValidator->field('contents'));
        $validator->requirePresence('contents', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('contents', __(Message::ERROR_NOT_EMPTY), false);

        $validator->field('footer', $defaultValidator->field('footer'));
        $validator->requirePresence('footer', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('footer', __(Message::ERROR_NOT_EMPTY), true);

        $validator->field('admin_operation_mail_flg', $defaultValidator->field('admin_operation_mail_flg'));
        $validator->field('header_admin', $defaultValidator->field('header_admin'));
        $validator->field('contents_admin', $defaultValidator->field('contents_admin'));
        $validator->field('footer_admin', $defaultValidator->field('footer_admin'));

        return $validator;
    }

    /**
     * フォーム項目名の返却
     *
     * @return array
     */
    public function getFormName()
    {
        return $this->formName;
    }
}
