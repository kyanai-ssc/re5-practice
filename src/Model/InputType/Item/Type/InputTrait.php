<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * Input trait.
 */
trait InputTrait
{
    /**
     * @var bool
     */
    protected $isInvalidData = false;

    /**
     * 項目の入力可否を判定
     *
     * @return bool 判定結果
     */
    public function canInput()
    {
        if (!$this->canDisplay()) {
            return false;
        }
        if (!isset($this->displayType['canInput'])) {
            return false;
        }

        return $this->displayType['canInput'];
    }

    /**
     * 入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array フィルタリング後の入力値
     */
    public function filterInputs(array $inputs)
    {
        return $inputs;
    }

    /**
     * 文字コード等の不正なデータ判定
     *
     * @return bool
     */
    public function isInvalidData()
    {
        return $this->isInvalidData;
    }

    /**
     * 入力値の入力判定
     *
     * @param mixed $value 値
     * @return bool
     */
    protected function isNotEmptyInput($value)
    {
        return is_string($value) && $value !== '';
    }
}
