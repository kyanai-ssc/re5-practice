<?php
declare(strict_types=1);

namespace App\Form\Admin\PassReset;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * パスワードリセットフォーム 承認
 */
class ApprovalForm extends AppForm
{
    public const LOGIN_ID_MAX = 1000;
    public const TOKEN_LENGTH = 64;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('token', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('token', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('token', __(Message::ERROR_NOT_EMPTY), false)
            ->add('token', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alphaNumeric' => [
                    'rule' => ['alphaNumeric'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALPHA_NUMERIC),
                ],
                'exits' => [
                    'rule' => function ($value) {
                        return $this->validToken($value);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EXISTS),
                ],
            ]);

        return $validator;
    }

    /**
     * トークンチェック
     *
     * @param mixed $value token
     * @return bool
     */
    public function validToken($value)
    {
        /** @var \App\Model\Table\AdminPassResetTokensTable $passResetTable */
        $passResetTable = $this->getTableLocator()->get('AdminPassResetTokens');
        $token = $passResetTable->find('Token', ['token' => $value]);

        if ($token->count() >= 1) {
            return true;
        }

        return false;
    }
}
