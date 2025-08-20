<?php
declare(strict_types=1);

namespace App\Form\User\Optin;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\OptinToken;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * オプトインフォーム
 */
class OptinForm extends AppForm
{
    public const TOKEN_MAX_LENGTH = 1000;

    /**
     * @var \App\Model\Entity\OptinToken|null
     */
    protected $optinToken = null;

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
                'maxLength' => [
                    'rule' => ['maxLength', static::TOKEN_MAX_LENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TOKEN_MAX_LENGTH),
                ],
                'exists' => [
                    'rule' => function () {
                        if (is_null($this->getOptinToken())) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        if ($this->commonData()->existsUserLoginData()) {
            $validator
                ->add('token', [
                    'checkUserId' => [
                        'rule' => function () {
                            if (!($this->getOptinToken() instanceof OptinToken)) {
                                throw new CakeException();
                            }
                            $userId = $this->getOptinToken()->get('user_id');
                            if ($userId === $this->commonData()->getUserLoginData()->get('id')) {
                                return true;
                            }

                            return false;
                        },
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * オプトイントークンを取得
     *
     * @return \App\Model\Entity\OptinToken|null
     */
    public function getOptinToken()
    {
        if (!isset($this->optinToken)) {
            /** @var \App\Model\Table\OptinTokensTable $optinTokensTable */
            $optinTokensTable = $this->getTableLocator()->get('OptinTokens');

            $optinToken = $optinTokensTable->find('token', [
                'token' => $this->getData('token'),
            ])->first();

            if ($optinToken instanceof OptinToken) {
                $this->optinToken = $optinToken;
            }
        }

        return $this->optinToken;
    }

    /**
     * オプトインのデータを取得
     *
     * @return array
     */
    public function getOptinData()
    {
        $optinToken = $this->getOptinToken();
        if (!isset($optinToken)) {
            throw new CakeException();
        }

        $optinData = [
            'optinToken' => [
                'token' => $optinToken->get('token'),
            ],
            'optinData' => [
                'users' => [
                    'mail' => $optinToken->get('mail'),
                ],
            ],
        ];

        return $optinData;
    }
}
