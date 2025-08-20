<?php
declare(strict_types=1);

namespace App\Model;

use App\Utility\ArrayUtility;
use ArrayObject;
use Cake\Utility\Hash;

/**
 * Inputs trait.
 */
trait InputsTrait
{
    /**
     * @var array|null
     */
    protected $fieldValueOptions = null;

    /**
     * @var array|null
     */
    protected $defaultFieldValues = null;

    /**
     * @var array
     */
    protected $inputFilters = [
        'filterTrailingWhitespace',
        'filterEmptyString',
    ];

    /**
     * フィールドの値リストを取得
     *
     * @param string|null $key キー
     * @return array 値リスト
     */
    public function getFieldValueOptions(?string $key = null)
    {
        if (!isset($this->fieldValueOptions)) {
            $this->fieldValueOptions = $this->buildFieldValueOptions();
        }
        if (isset($key) && $key !== '') {
            return Hash::get($this->fieldValueOptions, $key, []);
        }

        return $this->fieldValueOptions;
    }

    /**
     * フィールドの値リストを設定
     *
     * @param array $fieldValueOptions 値リスト
     * @return void
     */
    public function setFieldValueOptions(array $fieldValueOptions)
    {
        $this->fieldValueOptions = $fieldValueOptions;
    }

    /**
     * フィールドの値リストを構築
     *
     * @return array 値リスト
     */
    protected function buildFieldValueOptions()
    {
        return [];
    }

    /**
     * フィールドのデフォルト値を取得
     *
     * @param string|null $key キー
     * @param mixed $default デフォルト値
     * @return mixed デフォルト値
     */
    public function getDefaultFieldValues(?string $key = null, $default = null)
    {
        if (!isset($this->defaultFieldValues)) {
            $this->defaultFieldValues = $this->buildDefaultFieldValues();
        }
        if (isset($key) && $key !== '') {
            return Hash::get($this->defaultFieldValues, $key, $default);
        }

        return $this->defaultFieldValues;
    }

    /**
     * フィールドのデフォルト値を設定
     *
     * @param array $defaultFieldValues デフォルト値
     * @return void
     */
    public function setDefaultFieldValues(array $defaultFieldValues)
    {
        $this->defaultFieldValues = $defaultFieldValues;
    }

    /**
     * フィールドのデフォルト値を構築
     *
     * @return array デフォルト値
     */
    protected function buildDefaultFieldValues()
    {
        return [];
    }

    /**
     * 入力値をフィルタリングする
     *
     * @param \ArrayObject $inputs 入力値
     * @return void
     */
    public function filterInputs(ArrayObject $inputs)
    {
        $inputs->exchangeArray(ArrayUtility::arrayMapRecursive(function ($value) {
            foreach ($this->inputFilters as $filter) {
                $function = [$this, $filter];
                if (is_callable($function)) {
                    $value = call_user_func($function, $value);
                }
            }

            return $value;
        }, $inputs->getArrayCopy()));
    }

    /**
     * 末尾の空白を除去する
     *
     * @param mixed $data データ
     * @return mixed フィルタリング後のデータ
     */
    protected function filterTrailingWhitespace($data)
    {
        if (!is_scalar($data)) {
            return $data;
        }

        $value = preg_replace('/[\\s]+$/', '', (string)$data);

        return $value;
    }

    /**
     * 空文字をNULLへ変換する
     *
     * @param mixed $data データ
     * @return mixed フィルタリング後のデータ
     */
    protected function filterEmptyString($data)
    {
        if (!is_scalar($data)) {
            return $data;
        }

        if (((string)$data) === '') {
            return null;
        }

        return $data;
    }
}
