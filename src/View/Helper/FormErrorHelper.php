<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * FormErrorHelper class.
 *
 * @property \Cake\View\Helper\FormHelper $Form
 */
class FormErrorHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Form'];

    /**
     * ネストした階層を除いてエラーの有無をチェック
     *
     * @param string $field フィールド名
     * @return bool エラー有無
     */
    public function isFieldErrorWithoutNested(string $field)
    {
        $exists = false;
        foreach ($this->Form->context()->error($field) as $value) {
            if (!is_array($value)) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * ネストした階層を除いてエラーがある場合にエラーのclassを付与
     *
     * @param string $field フィールド名
     * @return array エラークラス
     */
    public function addFieldErrorClassWithoutNested(string $field)
    {
        $class = [];
        if ($this->isFieldErrorWithoutNested($field)) {
            $class['add'] = 'warning';
        }

        return $class;
    }

    /**
     * ネストした階層を除いてエラーメッセージを出力
     *
     * @param string $field フィールド名
     * @param array $options オプション
     * @return string エラーメッセージ
     */
    public function errorWithoutNested(string $field, array $options = [])
    {
        if (!$this->isFieldErrorWithoutNested($field)) {
            return '';
        }

        $error = [];
        foreach ($this->Form->context()->error($field) as $key => $value) {
            $error[$key] = '';
            if (!is_array($value)) {
                $error[$key] = $value;
            }
        }

        return $this->Form->error($field, $error, $options);
    }
}
