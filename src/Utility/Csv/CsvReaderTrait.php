<?php
declare(strict_types=1);

namespace App\Utility\Csv;

use App\Utility\Text\TextReaderTrait;
use Cake\Core\Exception\CakeException;

/**
 * CsvReader trait.
 */
trait CsvReaderTrait
{
    use TextReaderTrait;

    protected int $rowCount = 0;

    /**
     * ファイルからレコードを取得する
     *
     * @return array|null レコード
     */
    protected function readCsv(): ?array
    {
        if (!isset($this->fileHandle)) {
            throw new CakeException('error fgetcsv.');
        }

        $record = fgetcsv(
            $this->fileHandle,
            0,
            $this->getConfig('csvDelimiter'),
            $this->getConfig('csvEnclosure'),
            $this->getConfig('csvEscapeChar')
        );
        if ($record === false) {
            if (!feof($this->fileHandle)) {
                throw new CakeException('error fgetcsv.');
            }

            return null;
        }
        $this->rowCount++;

        return $this->convertCsvData($record);
    }

    /**
     * 読み込んだレコードを変換
     *
     * @param array $data レコード
     * @return array 変換後のレコード
     */
    protected function convertCsvData(array $data): array
    {
        if (!is_null($this->getConfig('csvColumns'))) {
            $record = [];
            foreach ($this->getConfig('csvColumns') as $index => $column) {
                $record[$column] = null;
                if (isset($data[$index]) && $data[$index] !== '') {
                    $record[$column] = $data[$index];
                }
            }
            $data = $record;
        }

        return $data;
    }
}
