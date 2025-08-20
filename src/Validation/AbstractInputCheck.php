<?php
declare(strict_types=1);

namespace App\Validation;

/**
 * InputCheck abstract class.
 */
abstract class AbstractInputCheck
{
    /**
     * 値のバリデーション
     *
     * @param mixed $value 値
     * @return bool
     */
    abstract public function validate($value);

    /**
     * エラーメッセージを取得
     *
     * @return string|null
     */
    abstract public function getError();
}
