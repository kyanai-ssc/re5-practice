<?php
declare(strict_types=1);

namespace App\Form\Api\Admin;

use App\Form\AppForm;
use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * チェックフォーム
 */
class CheckForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('access_token', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('access_token', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('access_token', __(Message::ERROR_NOT_EMPTY), false);

        return $validator;
    }
}
