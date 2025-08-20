<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager\Type;

use App\Locale\Message;

/**
 * AdditionType trait.
 */
trait AdditionTypeTrait
{
    /**
     * 付加文言のフォーム項目詳細バリデータを追加
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return void
     */
    protected function addWordFormItemDetailValidation($validator)
    {
        $validator
            ->requirePresence('front_word', false)
            ->allowEmptyString('front_word')
            ->add('front_word', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ITEM_DETAIL_FRONT_WORD_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ITEM_DETAIL_FRONT_WORD_MAX),
                ],
            ]);

        $validator
            ->requirePresence('back_word', false)
            ->allowEmptyString('back_word')
            ->add('back_word', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::ITEM_DETAIL_BACK_WORD_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::ITEM_DETAIL_BACK_WORD_MAX),
                ],
            ]);
    }
}
