<?php
declare(strict_types=1);

namespace App\Validation;

use App\Locale\Message;
use App\Model\Entity\Admin;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validation;

/**
 * Class PasswordValidation
 *
 * @package App\Validation
 */
class PasswordValidation extends Validation
{
    public const MIN = 8;
    public const MAX = 32;
    public const CHARACTER = '/^[\\x21-\\x7e]+$/';
    public const PATTERN = [
        '/[0-9]/',
        '/[A-Za-z]/',
    ];

    /**
     * パスワードの検証ルールを返却
     *
     * @param array $setting setting
     * @return mixed
     */
    public static function getCommonValidator(array $setting)
    {
        $min = Hash::get($setting, 'length.min', static::MIN);
        $max = Hash::get($setting, 'length.max', static::MAX);

        $validate['password'] = [
            'isScalar' => [
                'rule' => ['isScalar'],
                'last' => true,
                'message' => __(Message::ERROR_INVALID_VALUE),
            ],
            'minLength' => [
                'rule' => ['minLength', $min],
                'last' => true,
                'message' => __(Message::ERROR_MIN_LENGTH, $min),
            ],
            'maxLength' => [
                'rule' => ['maxLength', $max],
                'last' => true,
                'message' => __(Message::ERROR_MAX_LENGTH, $max),
            ],
            'custom' => [
                'rule' => ['custom', Hash::get($setting, 'character', static::CHARACTER)],
                'last' => true,
                'message' => __(Message::ERROR_PASSWORD_CHARACTER),
            ],
            'alphanumericMixture' => [
                'rule' => function ($value) use ($setting) {
                    foreach (Hash::get($setting, 'patterns', static::PATTERN) as $pattern) {
                        if (preg_match($pattern, $value) !== 1) {
                            return false;
                        }
                    }

                    return true;
                },
                'last' => true,
                'message' => __(Message::ERROR_PASSWORD_ALPHANUMERIC),
            ],
        ];

        $validate['confirm'] = [
            'isScalar' => [
                'rule' => ['isScalar'],
                'last' => true,
                'message' => __(Message::ERROR_INVALID_VALUE),
            ],
            'compareWith' => [
                'rule' => ['compareWith', 'password'],
                'last' => true,
                'message' => __(Message::ERROR_NOT_SAME),
            ],
        ];

        return $validate;
    }

    /**
     * 管理者のパスワードValidator
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @return \Cake\Validation\Validator
     */
    public static function getAdminValidator(Validator $validator)
    {
        $validate = static::getCommonValidator(Configure::readOrFail('Setting.auth.admin.password'));
        $validator
            ->requirePresence('password', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('password', __(Message::ERROR_NOT_EMPTY), function ($context) {
                //初回変更時と新規登録の場合必須
                if (!Hash::get($context['data'], 'id', false)) {
                    return false;
                }

                /** @var \App\Model\Table\AdminsTable $adminTable */
                $adminTable = $context['providers']['table'];

                try {
                    $admin = $adminTable->get($context['data']['id'], [
                        'finder' => 'auth',
                    ]);
                    if ($admin->isPassReset() === Admin::FIRST_TIME_RESET) {
                        return false;
                    }
                } catch (RecordNotFoundException $e) {
                    return false;
                }

                return true;
            })
            ->add('password', $validate['password']);

        $passwordConfirmRequired = function ($context) {
            $password = Hash::get($context['data'], 'password');
            if (isset($password)) {
                if (!is_scalar($password)) {
                    return true;
                }
                $password = (string)$password;
            } else {
                $password = null;
            }
            if (!$context['newRecord'] && ((string)$password === '')) {
                return true;
            }

            return false;
        };

        $validator
            ->requirePresence('password_confirm', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('password_confirm', __(Message::ERROR_NOT_EMPTY), $passwordConfirmRequired)
            ->add('password_confirm', $validate['confirm']);

        return $validator;
    }

    /**
     * 公開側ユーザーのパスワードValidator
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @param bool $confirm 確認表示
     * @return \Cake\Validation\Validator
     */
    public static function getUserValidator(Validator $validator, $confirm = true)
    {
        $validate = static::getCommonValidator(Configure::readOrFail('Setting.auth.user.password'));
        $validator
            ->requirePresence('password', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('password', __(Message::ERROR_NOT_EMPTY), false)
            ->add('password', $validate['password']);

        if ($confirm) {
            $validator
                ->requirePresence('password_confirm', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('password_confirm', __(Message::ERROR_NOT_EMPTY), false)
                ->add('password_confirm', $validate['confirm']);
        }

        return $validator;
    }
}
