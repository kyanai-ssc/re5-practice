<?php
declare(strict_types=1);

namespace App\Model;

use App\Utility\Csv\CsvWriter;
use App\Utility\Csv\StreamCsvWriter;
use App\Utility\CsvFormat\CsvFormatTrait;
use Throwable;

/**
 * CsvExport trait.
 */
trait CsvExportTrait
{
    use CsvFormatTrait;

    /**
     * CSV出力のストリーム用コールバックを取得
     *
     * @param array $header ヘッダ
     * @param callable $generator 出力内容の生成処理
     * @return callable コールバック
     */
    protected function getCsvStreamCallback($header, $generator)
    {
        $callback = function () use ($header, $generator) {
            $csvColumns = null;
            if (!empty($header)) {
                $csvColumns = array_keys($header);
            }

            $csvWriter = new StreamCsvWriter([
                'internalEncoding' => 'UTF-8',
                'fileEncoding' => 'UTF-8',
                'fileBom' => true,
                'fileLinefeed' => "\r\n",
                'csvColumns' => $csvColumns,
            ]);

            $this->outputCsv($csvWriter, $header, $generator);
        };

        return $callback;
    }

    /**
     * CSVを出力
     *
     * @param \App\Utility\Csv\CsvWriter $csvWriter CSV出力クラス
     * @param array $header ヘッダ
     * @param callable $generator 出力内容の生成処理
     * @return void
     */
    protected function outputCsv($csvWriter, $header, $generator): void
    {
        try {
            $csvWriter->open();
            if (!empty($header)) {
                $csvWriter->append($header);
            }
            foreach (call_user_func($generator) as $data) {
                $csvWriter->append($data);
            }
            $csvWriter->close();
        } catch (Throwable $e) {
            $csvWriter->close(true);
            throw $e;
        }
    }

    /**
     * CSVファイルを生成
     *
     * @param array $header ヘッダ
     * @param callable $generator 出力内容の生成処理
     * @param array $options オプション
     * @return string ファイルパス
     */
    public function createCsvFile(array $header, $generator, $options = []): string
    {
        $csvColumns = null;
        if (!empty($header)) {
            $csvColumns = array_keys($header);
        }

        $csvWriter = new CsvWriter($options + [
            'internalEncoding' => 'UTF-8',
            'fileEncoding' => 'UTF-8',
            'fileBom' => true,
            'fileLinefeed' => "\r\n",
            'csvColumns' => $csvColumns,
        ]);

        $this->outputCsv($csvWriter, $header, $generator);

        return $csvWriter->getConfig('filePath');
    }
}
