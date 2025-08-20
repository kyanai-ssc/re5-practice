<?php
declare(strict_types=1);

namespace App\Form\User\Guest;

use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * ログインフォーム
 */
class LoginForm extends AppForm
{
    public const MAIL_MAX = 254;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('reservation_id', 'int')
            ->addField('mail', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('reservation_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reservation_id', __(Message::ERROR_NOT_EMPTY), false)
            ->add('reservation_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        $validator
            ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'email' => [
                    'rule' => ['email'],
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                    'last' => true,
                ],
                'exists' => [
                    'rule' => [$this, 'reservationExists'],
                    'message' => __(Message::ERROR_NOT_MATCH_DATA),
                    'on' => function () use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        return $validator->isValid('reservation_id');
                    },
                    'last' => true,
                ],
            ]);

        return $validator;
    }

    /**
     * 予約ID存在チェック
     *
     * @param mixed $value id
     * @param mixed $context context
     * @return bool
     */
    public function reservationExists($value, $context)
    {
        $reservationId = Hash::get($context, 'data.reservation_id');

        /** @var \App\Model\Table\ReservationsTable $reservationTable */
        $reservationTable = $this->getTableLocator()->get('Reservations');

        return $reservationTable->hasGuestReserve($value, $reservationId);
    }
}
