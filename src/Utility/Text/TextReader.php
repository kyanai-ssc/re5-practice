<?php
declare(strict_types=1);

namespace App\Utility\Text;

/**
 * TextReader class.
 */
class TextReader
{
    use TextReaderTrait;

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
     * ファイルから1行取得する
     *
     * @return string|null 文字列
     */
    public function read(): ?string
    {
        return $this->readLine();
    }
}
