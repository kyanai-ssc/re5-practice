<?php
declare(strict_types=1);

namespace App\Form\Common\Auth;

use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ログインフォーム
 */
trait LoginFormTrait
{
    /**
     * スキーマ生成
     *
     * @param \Cake\Form\Schema $schema Schema
     * @return \Cake\Form\Schema
     */
    protected function buildLoginSchema(Schema $schema)
    {
        $schema
            ->addField('login_id', 'string')
            ->addField('password', 'string');

        return $schema;
    }

    /**
     * バリデータ生成
     *
     * @param \Cake\Validation\Validator $validator Validator
     * @param array $options オプション
     * @return \Cake\Validation\Validator
     */
    public function buildLoginValidator(Validator $validator, $options)
    {
        $validator
            ->requirePresence('login_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('login_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('login_id', [
                'maxLength' => [
                    'rule' => ['maxLength', Hash::get($options, 'loginIdMax')],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, Hash::get($options, 'loginIdMax')),
                ],
            ]);

        $validator
            ->requirePresence('password', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('password', __(Message::ERROR_NOT_EMPTY), false)
            ->add('password', [
                'maxLength' => [
                    'rule' => ['maxLength', Hash::get($options, 'passwordMax')],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, Hash::get($options, 'passwordMax')),
                ],
            ]);

        return $validator;
    }
}
