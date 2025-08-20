<?php
declare(strict_types=1);

namespace App\Utility\Text;

/**
 * TextWriter class.
 */
class TextWriter
{
    use TextWriterTrait;

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
    ];

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
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
     * ファイルから文字列を追記する
     *
     * @param string $filePath ファイルパス
     * @param string|null $fileEncoding ファイルエンコーディング
     * @return void
     */
    public function appendFromFile(string $filePath, ?string $fileEncoding = null): void
    {
        $this->appendTextFromFile($filePath, $fileEncoding);
    }
}
