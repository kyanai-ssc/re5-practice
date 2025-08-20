<?php
declare(strict_types=1);

namespace App\Form\User\User;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\User;
use App\Validation\PasswordValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validation;
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
            ->addField('old_password', 'string')
            ->addField('password', 'string')
            ->addField('password_confirm', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $passwordLength = Configure::readOrFail('Setting.auth.user.password.length.max');

        $validator
            ->requirePresence('old_password', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('old_password', __(Message::ERROR_NOT_EMPTY), false)
            ->add('old_password', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', $passwordLength],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, $passwordLength),
                ],
                'exists' => [
                    'rule' => function ($value) {
                        return $this->existsPassword($value);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOW_PASSWORD_NOT_SAME),
                ],
            ]);

        $validator = PasswordValidation::getUserValidator($validator, true);

        $validator->add('password', [
            'compareWith' => [
                'rule' => ['compareFields', 'old_password', Validation::COMPARE_NOT_EQUAL],
                'last' => true,
                'message' => __(Message::ERROR_PASSWORD_SAME),
            ],
        ]);

        return $validator;
    }

    /**
     * 会員IDと現在のパスワードが一致するか
     *
     * @param string $value password
     * @return bool
     */
    public function existsPassword($value)
    {
        /** @var \App\Model\Table\UsersTable $userTable */
        $userTable = $this->getTableLocator()->get('Users');

        if ($this->commonData()->existsUserLoginData()) {
            $user = $userTable->get($this->commonData()->getUserLoginData()->get('id'), [
                'finder' => 'auth',
            ]);

            if ($user instanceof User) {
                if ($user->passwordCheck($value)) {
                    return true;
                }
            }
        }

        return false;
    }
}
