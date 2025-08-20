<?php
declare(strict_types=1);

namespace App\Form\Admin\MailDeliveries;

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
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('mail', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail', [
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

        return $validator;
    }
}
