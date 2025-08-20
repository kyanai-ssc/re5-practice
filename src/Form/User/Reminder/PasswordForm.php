<?php
declare(strict_types=1);

namespace App\Form\User\Reminder;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\PasswordValidation;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * リマインダー
 */
class PasswordForm extends AppForm
{
    /**
     * @var int|null
     */
    protected $userId = null;

    public const LOGIN_ID_MAX = 1000;
    public const TOKEN_LENGTH = 64;

    /**
     * 会員IDのセット
     *
     * @param int $userId 会員ID
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('login_id', 'string')
            ->addField('password', 'string')
            ->addField('password_confirm', 'string');

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
                'exists' => [
                    'rule' => function ($value) {
                        return $this->existsUser($value);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_SAME),
                ],
            ]);

        $validator = PasswordValidation::getUserValidator($validator, true);

        return $validator;
    }

    /**
     * 会員IDとログインIDが一致するかのチェック
     *
     * @param string $value user_id
     * @return bool
     */
    public function existsUser($value)
    {
        /** @var \App\Model\Table\UsersTable $userTable */
        $userTable = $this->getTableLocator()->get('Users');
        $user = $userTable->find('reminderUser', [
            'userId' => $this->userId,
            'loginId' => $value,
        ]);

        if ($user->count() >= 1) {
            return true;
        }

        return false;
    }
}
