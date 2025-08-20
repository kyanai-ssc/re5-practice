<?php
declare(strict_types=1);

namespace App\Form\User\Guest;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\ReservationGuestCode;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 認証フォーム
 */
class CodeForm extends AppForm
{
    /**
     * @var int 予約ID
     */
    protected $reId;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('code', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('code', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('code', __(Message::ERROR_NOT_EMPTY), false)
            ->add('code', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'code' => [
                    'rule' => ['maxLength', ReservationGuestCode::CODE_MAXLENGTH],
                    'message' => __(Message::ERROR_MAX_LENGTH, ReservationGuestCode::CODE_MAXLENGTH),
                    'last' => true,
                ],
                'exists' => [
                    'rule' => [$this, 'codeExists'],
                    'message' => __(Message::ERROR_NOT_MATCH_DATA),
                    'last' => true,
                ],
            ]);

        return $validator;
    }

    /**
     * 認証チェック
     *
     * @param mixed $value id
     * @param mixed $context context
     * @return bool
     */
    public function codeExists($value, $context)
    {
        /** @var \App\Model\Table\ReservationGuestCodesTable $codeTable */
        $codeTable = $this->getTableLocator()->get('ReservationGuestCodes');

        return $codeTable->existsReId($this->getReId(), $value);
    }

    /**
     * @return int
     */
    public function getReId()
    {
        return $this->reId;
    }

    /**
     * 予約IDをセット
     *
     * @param int $reId ID
     * @return void
     */
    public function setReId($reId)
    {
        $this->reId = $reId;
    }
}
