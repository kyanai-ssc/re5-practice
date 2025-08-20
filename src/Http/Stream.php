<?php
declare(strict_types=1);

namespace App\Http;

use Laminas\Diactoros\Stream as HttpStream;

/**
 * Stream class.
 */
class Stream extends HttpStream
{
    /**
     * @var bool
     */
    protected $cleanup = false;

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->cleanupFile();
    }

    /**
     * 終了時の削除有無を取得
     *
     * @return bool 削除有無
     */
    public function isCleanup()
    {
        return $this->cleanup;
    }

    /**
     * 終了時の削除有無を設定
     *
     * @param bool $cleanup 削除有無
     * @return void
     */
    public function setCleanup(bool $cleanup)
    {
        $this->cleanup = $cleanup;
    }

    /**
     * ファイルを削除
     *
     * @return void
     */
    protected function cleanupFile()
    {
        if ($this->isCleanup() && !is_resource($this->stream)) {
            $this->close();
            if (is_string($this->stream)) {
                unlink($this->stream);
            }
        }
    }
}
