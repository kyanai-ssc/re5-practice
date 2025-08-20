<?php
declare(strict_types=1);

namespace App\Form\Admin\PassReset;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * パスワードリセットフォーム 管理者参照
 */
class CertificationForm extends AppForm
{
    public const LOGIN_ID_MAX = 1000;
    public const TOKEN_LENGTH = 64;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('login_id', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('login_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('login_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('login_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::LOGIN_ID_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::LOGIN_ID_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', Configure::readOrFail('Setting.auth.admin.loginId.character')],
                    'last' => true,
                    'message' => __(Message::ERROR_LOGIN_ID_CHARACTER),
                ],
            ]);

        return $validator;
    }
}
