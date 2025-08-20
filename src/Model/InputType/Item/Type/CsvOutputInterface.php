<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * CsvOutput interface.
 */
interface CsvOutputInterface
{
    /**
     * CSVの出力可否を判定
     *
     * @return bool 出力可否
     */
    public function canCsvOutput();

    /**
     * CSVの出力内容を取得
     *
     * @param array|null $options オプション引数
     * @return string|null 出力内容
     */
    public function getCsvOutputValue(?array $options = null);
}
