<?php
declare(strict_types=1);

namespace App\Form\Api\Admin;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * ログインフォーム
 */
class LoginForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('login_id', 'string')
            ->addField('password', 'string')
            ->addField('api_secret', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('login_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('login_id', __(Message::ERROR_NOT_EMPTY), false);

        $validator
            ->requirePresence('password', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('password', __(Message::ERROR_NOT_EMPTY), false);

        $validator
            ->requirePresence('api_secret', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('api_secret', __(Message::ERROR_NOT_EMPTY), false);

        return $validator;
    }
}
