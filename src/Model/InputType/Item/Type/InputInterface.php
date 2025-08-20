<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use Cake\Validation\Validator;

/**
 * Input interface.
 */
interface InputInterface
{
    /**
     * 項目の入力可否を判定
     *
     * @return bool 判定結果
     */
    public function canInput();

    /**
     * 入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array フィルタリング後の入力値
     */
    public function filterInputs(array $inputs);

    /**
     * 入力時のバリデータを構築
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator バリデータ
     */
    public function buildFieldsetValidator(Validator $validator);

    /**
     * 文字コード等の不正なデータ判定
     *
     * @return bool
     */
    public function isInvalidData();
}
