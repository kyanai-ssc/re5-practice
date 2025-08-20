<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager\Type;

use App\Locale\Message;
use Cake\Core\Exception\CakeException;
use Cake\Validation\Validation;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * TextType trait.
 */
trait TextTypeTrait
{
    use AdditionTypeTrait;

    /**
     * テキスト項目のフォーム項目詳細バリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator
     */
    protected function validationTextFormItemDetail($validator)
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getTableLocator()->get('FormItemDetails');

        $this->addWordFormItemDetailValidation($validator);

        $validator
            ->requirePresence('text_lower_limit', false)
            ->allowEmptyString('text_lower_limit')
            ->add('text_lower_limit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'compareGreaterOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        static::ITEM_DETAIL_TEXT_LOWER_LIMIT_MIN,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, static::ITEM_DETAIL_TEXT_LOWER_LIMIT_MIN),
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::ITEM_DETAIL_TEXT_LOWER_LIMIT_MAX,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::ITEM_DETAIL_TEXT_LOWER_LIMIT_MAX),
                ],
            ]);

        $validator
            ->requirePresence('text_upper_limit', false)
            ->allowEmptyString('text_upper_limit')
            ->add('text_upper_limit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'compareGreaterOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        static::ITEM_DETAIL_TEXT_UPPER_LIMIT_MIN,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, static::ITEM_DETAIL_TEXT_UPPER_LIMIT_MIN),
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::ITEM_DETAIL_TEXT_UPPER_LIMIT_MAX,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::ITEM_DETAIL_TEXT_UPPER_LIMIT_MAX),
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'text_lower_limit', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_LESS_THAN_FROM),
                    'on' => function () use ($validator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $validator->isValid('text_lower_limit');
                    },
                ],
            ]);

        $validator
            ->requirePresence('text_input_translate', false)
            ->allowEmptyString('text_input_translate')
            ->add('text_input_translate', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($formItemDetailsTable->getFieldValueOptions('textInputTranslate'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('text_input_check', false)
            ->allowEmptyString('text_input_check')
            ->add('text_input_check', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($formItemDetailsTable->getFieldValueOptions('textInputCheck'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }
}
