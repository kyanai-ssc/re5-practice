<?php
declare(strict_types=1);

namespace App\Utility\Csv;

use App\Utility\Text\TextWriterTrait;
use Cake\Core\Exception\CakeException;

/**
 * CsvWriter trait.
 */
trait CsvWriterTrait
{
    use TextWriterTrait;

    /**
     * レコード追記する
     *
     * @param array $data レコード
     * @return void
     */
    protected function appendCsv(array $data): void
    {
        if (!isset($this->fileHandle)) {
            throw new CakeException('error fputcsv.');
        }

        $result = fputcsv(
            $this->fileHandle,
            $this->convertCsvData($data),
            $this->getConfig('csvDelimiter'),
            $this->getConfig('csvEnclosure'),
            $this->getConfig('csvEscapeChar')
        );
        if ($result === false) {
            throw new CakeException('error fputcsv.');
        }
    }

    /**
     * 書き込み用にレコードを変換
     *
     * @param array $data レコード
     * @return array 変換後のレコード
     */
    protected function convertCsvData(array $data): array
    {
        if (!is_null($this->getConfig('csvColumns'))) {
            $record = [];
            foreach ($this->getConfig('csvColumns') as $index => $column) {
                $record[$index] = null;
                if (isset($data[$column]) && $data[$column] !== '') {
                    $record[$index] = $data[$column];
                }
            }
            $data = $record;
        }

        return $data;
    }
}
