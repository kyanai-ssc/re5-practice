<?php
declare(strict_types=1);

namespace App\Command\Traits;

use App\Utility\Csv\CsvReader;
use App\Utility\Database\BuilderFactory;
use Cake\Core\Exception\CakeException;
use Cake\Database\Driver\Postgres;
use Cake\Http\Exception\BadRequestException;
use Migrations\CakeAdapter;

/**
 * Seed trait.
 */
trait SeedTrait
{
    use ClientTrait;

    /**
     * @var bool
     */
    protected $setLocale = false;

    /**
     * クライアントのチェック
     *
     * @return void
     */
    protected function checkClient(): void
    {
        if (!$this->checkClientOption()) {
            throw new BadRequestException('invalid client.');
        }
    }

    /**
     * CSVからデータを取得
     *
     * @param string $filePath ファイルパス
     * @param array $columns カラム
     * @param array|null $options オプション
     * @return array
     */
    protected function readCsv($filePath, $columns, $options = null)
    {
        $data = [];
        foreach ($this->readCsvEach($filePath, $columns, $options) as $line) {
            $data[] = reset($line);
        }

        return $data;
    }

    /**
     * CSVから指定行ずつデータを取得
     *
     * @param string $filePath ファイルパス
     * @param array $columns カラム
     * @param array|null $options オプション
     * @return \Traversable
     */
    protected function readCsvEach($filePath, $columns, $options = null)
    {
        if (strpos(PHP_OS, 'WIN') === 0 && !$this->setLocale) {
            setlocale(LC_CTYPE, 'C');
            $this->setLocale = true;
        }

        $options = (array)$options + [
                'ignoreHeader' => true,
                'count' => 1,
            ];

        $csvReader = new CsvReader([
            'internalEncoding' => 'UTF-8',
            'fileEncoding' => 'UTF-8',
            'fileBom' => true,
            'filePath' => $filePath,
            'csvColumns' => $columns,
        ]);

        $csvReader->open();
        $existsRow = true;
        if ($options['ignoreHeader']) {
            $line = $csvReader->read();
            if (!isset($line)) {
                $existsRow = false;
            }
        }

        if ($existsRow) {
            $data = [];
            while (true) {
                $line = $csvReader->read();
                if (!isset($line)) {
                    break;
                }
                $data[] = $line;
                if (count($data) >= $options['count']) {
                    yield $data;
                    $data = [];
                }
            }
            if (!empty($data)) {
                yield $data;
                unset($data);
            }
        }
        $csvReader->close();
    }

    /**
     * データを削除
     *
     * @param string $table テーブル名
     * @param bool $resetSequence シーケンスリセット
     * @return void
     */
    protected function deleteData($table, $resetSequence = true)
    {
        $query = $this->getAdapter()->getQueryBuilder();
        $query->delete($table);
        $query->execute();
        if ($resetSequence) {
            $this->resetSequence($table);
        }
    }

    /**
     * シーケンスを設定
     *
     * @param string $table テーブル名
     * @param string|null $column カラム名
     * @param string|null $sequence シーケンス名
     * @return void
     */
    protected function setSequence($table, $column = null, $sequence = null)
    {
        $adapter = $this->getAdapter();
        if (!($adapter instanceof CakeAdapter)) {
            throw new CakeException();
        }

        if (!($adapter->getCakeConnection()->getDriver() instanceof Postgres)) {
            return;
        }

        if (!isset($column)) {
            $column = 'id';
        }
        if (!isset($sequence)) {
            $sequence = $table . '_' . $column . '_seq';
        }

        $idQuery = $this->getAdapter()->getQueryBuilder();
        $idQuery->select([
            $idQuery->func()->max($column),
        ]);
        $idQuery->from($table);

        $query = $this->getAdapter()->getQueryBuilder();
        $query->select([
            $this->driverExpression()->setSequenceValue($sequence, $idQuery->sql($idQuery->getValueBinder())),
        ]);
        $query->execute()->fetchAll();
    }

    /**
     * シーケンスをリセット
     *
     * @param string $table テーブル名
     * @param string|null $column カラム名
     * @param string|null $sequence シーケンス名
     * @return void
     */
    protected function resetSequence($table, $column = null, $sequence = null)
    {
        $adapter = $this->getAdapter();
        if (!($adapter instanceof CakeAdapter)) {
            throw new CakeException();
        }

        if (!($adapter->getCakeConnection()->getDriver() instanceof Postgres)) {
            return;
        }

        if (!isset($column)) {
            $column = 'id';
        }
        if (!isset($sequence)) {
            $sequence = $table . '_' . $column . '_seq';
        }

        $query = $this->getAdapter()->getQueryBuilder();
        $query->select([
            $this->driverExpression()->setSequenceValue($sequence, '1', false),
        ]);
        $query->execute()->fetchAll();
    }

    /**
     * ドライバ固有のSQLを生成するビルダーを取得
     *
     * @return \App\Utility\Database\AbstractBuilder ビルダー
     */
    protected function driverExpression()
    {
        $adapter = $this->getAdapter();
        if (!($adapter instanceof CakeAdapter)) {
            throw new CakeException();
        }

        $driverClass = namespaceSplit((string)get_class($adapter->getCakeConnection()->getDriver()));

        return BuilderFactory::getInstance($driverClass[1]);
    }
}
