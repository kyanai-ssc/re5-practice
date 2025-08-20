<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * CsvInput interface.
 */
interface CsvInputInterface
{
    /**
     * CSVの入力値を取得
     *
     * @param string|null $data データ
     * @return array|null 入力値
     */
    public function formatCsvInputData(?string $data = null);

    /**
     * CSVのエラーメッセージを取得
     *
     * @param array $errors エラー
     * @return array|null エラーメッセージ
     */
    public function formatCsvErrors(array $errors);

    /**
     * CSVの説明文を取得
     *
     * @param array|null $options オプション引数
     * @return string|null 説明文
     */
    public function getCsvDescription(?array $options = null);
}
