<?php
declare(strict_types=1);

namespace App\Utility\Text;

use App\Utility\FileUtility;
use App\Utility\StringUtility;
use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use SplFileInfo;
use Throwable;

/**
 * TextWriter trait.
 */
trait TextWriterTrait
{
    use InstanceConfigTrait;

    protected ?SplFileInfo $temporaryFile;

    /**
     * @var resource|null
     */
    protected $fileHandle;

    /**
     * @var callable|null
     */
    protected $fileConverter;

    /**
     * ファイルを開く
     *
     * @return void
     */
    protected function openFile(): void
    {
        $temporaryFile = null;
        $fileHandle = null;
        try {
            if (is_null($this->getConfig('temporaryDirectory'))) {
                throw new CakeException('error tempnam.');
            }

            $temporaryFile = FileUtility::createTempFile(
                $this->getConfig('temporaryDirectory'),
                'txt',
                $this->getConfig('filePermission')
            );
            $this->temporaryFile = $temporaryFile;

            $fileHandle = fopen($temporaryFile->getPathname(), 'wb');
            if ($fileHandle === false) {
                throw new CakeException('error fopen.');
            }
            $this->fileHandle = $fileHandle;

            if ($this->getConfig('fileBom') && !isset($this->fileConverter)) {
                if (!fwrite($this->fileHandle, "\xEF\xBB\xBF")) {
                    throw new CakeException('error fwrite.');
                }
            }
        } catch (Throwable $e) {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
            $this->fileHandle = null;

            if (isset($temporaryFile)) {
                FileUtility::deleteFile($temporaryFile->getPathname());
            }
            $this->temporaryFile = null;

            throw $e;
        }
    }

    /**
     * ファイルを閉じる
     *
     * @param bool $delete 削除有無
     * @return void
     */
    protected function closeFile(bool $delete = false): void
    {
        try {
            if (isset($this->fileHandle) && is_resource($this->fileHandle)) {
                $fileHandle = $this->fileHandle;
                if (!fclose($fileHandle)) {
                    throw new CakeException('error fclose.');
                }
            }
            $this->fileHandle = null;

            if (isset($this->temporaryFile)) {
                if (!$delete) {
                    if (isset($this->fileConverter)) {
                        $convertedFile = null;
                        try {
                            $convertedFile = call_user_func($this->fileConverter);
                            if (isset($convertedFile)) {
                                FileUtility::deleteFile($this->temporaryFile->getPathname());
                                $this->temporaryFile = $convertedFile;
                            }
                        } catch (Throwable $e2) {
                            if (isset($convertedFile)) {
                                FileUtility::deleteFile($convertedFile->getPathname());
                            }
                            throw $e2;
                        }
                    }
                    if (!is_null($this->getConfig('filePath'))) {
                        FileUtility::copyFile($this->temporaryFile->getPathname(), $this->getConfig('filePath'));
                        FileUtility::deleteFile($this->temporaryFile->getPathname());
                    } else {
                        $this->setConfig('filePath', $this->temporaryFile->getPathname());
                    }
                    if (!is_null($this->getConfig('filePermission'))) {
                        FileUtility::changePermission($this->getConfig('filePath'), $this->getConfig('filePermission'));
                    }
                } else {
                    FileUtility::deleteFile($this->temporaryFile->getPathname());
                }
            }
            $this->temporaryFile = null;
        } catch (Throwable $e) {
            if (isset($this->fileHandle) && is_resource($this->fileHandle)) {
                $fileHandle = $this->fileHandle;
                fclose($fileHandle);
            }
            $this->fileHandle = null;

            if (isset($this->temporaryFile)) {
                FileUtility::deleteFile($this->temporaryFile->getPathname());
            }
            $this->temporaryFile = null;

            throw $e;
        }
    }

    /**
     * 文字列を追記する
     *
     * @param string $text 文字列
     * @return void
     */
    protected function appendText(string $text): void
    {
        if (!isset($this->fileHandle)) {
            throw new CakeException('error fwrite.');
        }

        if (!fwrite($this->fileHandle, $this->convertTextData($text))) {
            throw new CakeException('error fwrite.');
        }
    }

    /**
     * ファイルから文字列を追記する
     *
     * @param string $filePath ファイルパス
     * @param string|null $fileEncoding ファイルエンコーディング
     * @return void
     */
    protected function appendTextFromFile(string $filePath, ?string $fileEncoding = null): void
    {
        $textReader = null;
        try {
            $textReader = new TextReader([
                'internalEncoding' => $this->getConfig('internalEncoding'),
                'fileEncoding' => $fileEncoding,
                'filePath' => $filePath,
            ]);

            $textReader->open();
            while (true) {
                $data = $textReader->read();
                if (!isset($data)) {
                    break;
                }
                $this->appendText($data);
            }

            $textReader->close();
            $textReader = null;
        } catch (Throwable $e) {
            if (isset($textReader)) {
                $textReader->close();
            }
            throw $e;
        }
    }

    /**
     * 書き込み用に文字列を変換
     *
     * @param string $text 文字列
     * @return string 変換後の文字列
     */
    protected function convertTextData(string $text): string
    {
        if (!is_null($this->getConfig('fileLinefeed'))) {
            $text = StringUtility::replaceLinefeed($text, $this->getConfig('fileLinefeed'));
        }
        if (
            !is_null($this->getConfig('fileEncoding'))
            && $this->getConfig('fileEncoding') !== $this->getConfig('internalEncoding')
        ) {
            $text = mb_convert_encoding($text, $this->getConfig('fileEncoding'), $this->getConfig('internalEncoding'));
        }

        return $text;
    }
}
