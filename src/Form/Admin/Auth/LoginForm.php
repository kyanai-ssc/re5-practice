<?php
declare(strict_types=1);

namespace App\Form\Admin\Auth;

use App\Form\AppForm;
use App\Form\Common\Auth\LoginFormTrait;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * ログインフォーム
 */
class LoginForm extends AppForm
{
    use LoginFormTrait;

    public const LOGIN_ID_MAX = 1000;
    public const PASSWORD_MAX = 1000;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildLoginSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $options = [
            'loginIdMax' => static::LOGIN_ID_MAX,
            'passwordMax' => static::PASSWORD_MAX,
        ];

        $this->buildLoginValidator($validator, $options);

        return $validator;
    }
}
