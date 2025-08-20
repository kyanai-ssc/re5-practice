<?php
declare(strict_types=1);

namespace App\Model\InputType;

/**
 * InputTypeManager trait.
 */
trait InputTypeManagerTrait
{
    /**
     * 入力タイプマネージャを取得
     *
     * @param int $inputType 入力タイプ
     * @param bool $isAdmin 管理者側フラグ
     * @return \App\Model\InputType\AbstractInputTypeManager 入力タイプマネージャ
     */
    protected function inputTypeManager(int $inputType, bool $isAdmin)
    {
        return InputTypeManagerFactory::getInstance($inputType, $isAdmin);
    }
}
