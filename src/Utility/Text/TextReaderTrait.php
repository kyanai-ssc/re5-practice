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
 * TextReader trait.
 */
trait TextReaderTrait
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
            $filePath = $this->getConfig('filePath');
            if (!isset($filePath)) {
                throw new CakeException('file path is empty.');
            }

            if (isset($this->fileConverter)) {
                $temporaryFile = call_user_func($this->fileConverter);
                if (isset($temporaryFile)) {
                    $this->temporaryFile = $temporaryFile;
                    $filePath = $temporaryFile->getPathname();
                }
            }

            $fileHandle = fopen($filePath, 'rb');
            if ($fileHandle === false) {
                throw new CakeException('error fopen.');
            }
            $this->fileHandle = $fileHandle;

            if ($this->getConfig('fileBom')) {
                $bom = fread($this->fileHandle, 3);
                if ($bom === false) {
                    throw new CakeException('error fread.');
                }
                if ($bom !== "\xEF\xBB\xBF") {
                    if (fseek($this->fileHandle, 0) === -1) {
                        throw new CakeException('error fseek.');
                    }
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
     * @return void
     */
    protected function closeFile(): void
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
                FileUtility::deleteFile($this->temporaryFile->getPathname());
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
     * ファイルから1行取得する
     *
     * @return string|null 文字列
     */
    protected function readLine(): ?string
    {
        if (!isset($this->fileHandle)) {
            throw new CakeException('error fgets.');
        }

        $text = fgets($this->fileHandle);
        if ($text === false) {
            if (!feof($this->fileHandle)) {
                throw new CakeException('error fgets.');
            }

            return null;
        }

        return $this->convertTextData($text);
    }

    /**
     * 読み込んだ文字列を変換
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
            $text = mb_convert_encoding($text, $this->getConfig('internalEncoding'), $this->getConfig('fileEncoding'));
        }

        return $text;
    }
}
