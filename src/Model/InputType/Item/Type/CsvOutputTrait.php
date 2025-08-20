<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * CsvOutput trait.
 */
trait CsvOutputTrait
{
    /**
     * CSVの出力可否を判定
     *
     * @return bool 出力可否
     */
    public function canCsvOutput()
    {
        return true;
    }
}
