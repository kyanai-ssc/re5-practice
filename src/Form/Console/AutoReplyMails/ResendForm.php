<?php
declare(strict_types=1);

namespace App\Form\Console\AutoReplyMails;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 自動返信メール再送フォーム
 */
class ResendForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('id', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('id', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => function ($value) {
                        foreach ($value as $id) {
                            if (!CustomValidation::integer($id, CustomValidation::BIGINT_MAX)) {
                                return false;
                            }
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'exists' => [
                    'rule' => function ($value) {
                        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
                        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

                        $count = $autoReplyMailHistoriesTable->find('resend', [
                            'inputs' => [
                                'id' => $value,
                            ],
                        ])->count();
                        if ($count !== count($value)) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_EXISTS),
                ],
                'typeInList' => [
                    'rule' => function ($value) {
                        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
                        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

                        $count = $autoReplyMailHistoriesTable->find('resend', [
                            'inputs' => [
                                'id' => $value,
                            ],
                            'checkType' => true,
                        ])->count();
                        if ($count !== count($value)) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }
}
