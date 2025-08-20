<?php
declare(strict_types=1);

namespace App\Utility\Csv;

use App\Utility\FileUtility;
use App\Utility\Text\TextWriter;
use Cake\Core\Exception\CakeException;
use SplFileInfo;
use Throwable;

/**
 * CsvReader class.
 */
class CsvReader
{
    use CsvReaderTrait;

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'internalEncoding' => 'UTF-8',
        'fileEncoding' => null,
        'fileBom' => false,
        'fileLinefeed' => null,
        'filePath' => null,
        'filePermission' => null,
        'temporaryDirectory' => TMP,
        'csvDelimiter' => ',',
        'csvEnclosure' => '"',
        'csvEscapeChar' => '\\',
        'csvColumns' => null,
    ];

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
        $this->fileConverter = [$this, 'convertInputFile'];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * ファイルを開く
     *
     * @return void
     */
    public function open(): void
    {
        $this->close();
        $this->openFile();
    }

    /**
     * ファイルを閉じる
     *
     * @return void
     */
    public function close(): void
    {
        $this->closeFile();
    }

    /**
     * ファイルからレコードを取得する
     *
     * @return array|null レコード
     */
    public function read(): ?array
    {
        return $this->readCsv();
    }

    /**
     * 行カウント
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * 入力ファイルを変換
     *
     * @return \SplFileInfo|null ファイル
     */
    protected function convertInputFile(): ?SplFileInfo
    {
        if (
            is_null($this->getConfig('fileEncoding'))
            || $this->getConfig('fileEncoding') === $this->getConfig('internalEncoding')
        ) {
            return null;
        }

        $temporaryFile = null;
        $textWriter = null;
        try {
            if (is_null($this->getConfig('temporaryDirectory'))) {
                throw new CakeException('error tempnam.');
            }

            $temporaryFile = FileUtility::createTempFile(
                $this->getConfig('temporaryDirectory'),
                'csv',
                $this->getConfig('filePermission')
            );

            if (is_null($this->getConfig('filePath'))) {
                throw new CakeException('error filePath is null.');
            }
            $textWriter = new TextWriter([
                'internalEncoding' => $this->getConfig('internalEncoding'),
                'fileEncoding' => $this->getConfig('internalEncoding'),
                'filePath' => $temporaryFile->getPathname(),
            ]);
            $textWriter->open();
            $textWriter->appendFromFile($this->getConfig('filePath'), $this->getConfig('fileEncoding'));

            $textWriter->close();
            $textWriter = null;
        } catch (Throwable $e) {
            if (isset($textWriter)) {
                $textWriter->close();
            }
            if (isset($temporaryFile)) {
                FileUtility::deleteFile($temporaryFile->getPathname());
            }
            throw $e;
        }

        return $temporaryFile;
    }
}
