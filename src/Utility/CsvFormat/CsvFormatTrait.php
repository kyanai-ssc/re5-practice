<?php
declare(strict_types=1);

namespace App\Utility\CsvFormat;

/**
 * CsvFormat trait.
 */
trait CsvFormatTrait
{
    protected ?CsvFormatter $csvFormatter;

    /**
     * CSVフォーマット用のオブジェクトを取得
     *
     * @return \App\Utility\CsvFormat\CsvFormatter
     */
    protected function csvFormat(): CsvFormatter
    {
        if (!isset($this->csvFormatter)) {
            $this->csvFormatter = new CsvFormatter();
        }

        return $this->csvFormatter;
    }
}
