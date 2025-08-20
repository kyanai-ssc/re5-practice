<?php
declare(strict_types=1);

namespace App\Utility\Csv;

use App\Utility\FileUtility;
use App\Utility\Text\TextWriter;
use Cake\Core\Exception\CakeException;
use SplFileInfo;
use Throwable;

/**
 * CsvWriter class.
 */
class CsvWriter
{
    use CsvWriterTrait;

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
        $this->fileConverter = [$this, 'convertOutputFile'];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->close(true);
    }

    /**
     * ファイルを開く
     *
     * @return void
     */
    public function open(): void
    {
        $this->close(true);
        $this->openFile();
    }

    /**
     * ファイルを閉じる
     *
     * @param bool $delete 削除有無
     * @return void
     */
    public function close(bool $delete = false): void
    {
        $this->closeFile($delete);
    }

    /**
     * レコード追記する
     *
     * @param array $data レコード
     * @return void
     */
    public function append(array $data): void
    {
        $this->appendCsv($data);
    }

    /**
     * 出力ファイルを変換
     *
     * @return \SplFileInfo|null ファイル
     */
    protected function convertOutputFile(): ?SplFileInfo
    {
        $temporaryFile = null;
        $textWriter = null;
        try {
            if (!isset($this->temporaryFile) || is_null($this->getConfig('temporaryDirectory'))) {
                throw new CakeException('error tempnam.');
            }

            $temporaryFile = FileUtility::createTempFile(
                $this->getConfig('temporaryDirectory'),
                'csv',
                $this->getConfig('filePermission')
            );

            $textWriter = new TextWriter([
                'internalEncoding' => $this->getConfig('internalEncoding'),
                'fileEncoding' => $this->getConfig('fileEncoding'),
                'fileBom' => $this->getConfig('fileBom'),
                'fileLinefeed' => $this->getConfig('fileLinefeed'),
                'filePath' => $temporaryFile->getPathname(),
            ]);
            $textWriter->open();
            $textWriter->appendFromFile($this->temporaryFile->getPathname(), $this->getConfig('internalEncoding'));

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
