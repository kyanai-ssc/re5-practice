<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager\Type;

/**
 * SelectType trait.
 */
trait SelectTypeTrait
{
    use AdditionTypeTrait;

    /**
     * 選択肢項目のフォーム項目詳細バリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator
     */
    protected function validationSelectFormItemDetail($validator)
    {
        $this->addWordFormItemDetailValidation($validator);

        return $validator;
    }
}
