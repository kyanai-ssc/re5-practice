<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Locale\Message;
use App\Utility\FileUtility;
use Cake\Http\Exception\NotFoundException;
use SplFileInfo;

/**
 * File controller
 *
 * DBの参照やセッションの取得など一切行わずファイルのあるなしで判断する
 */
class FileController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->loadComponent('FileDownload');
        $this->loadComponent('Frame');

        if (!is_null(env(static::ENV_X_FRAME_OPTIONS_UNSET))) {
            $this->Frame->sameorigin();
        }

        $this->canAccess('user');
    }

    /**
     * Index method
     *
     * @param string $dirName ディレクトリ
     * @param string $fileName ファイル名
     * @return \Cake\Http\Response|null|void
     */
    public function index($dirName = null, $fileName = null)
    {
        if (is_null($dirName) || is_null($fileName)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $file = new SplFileInfo(UPLOAD_FILES . $dirName . DS . $fileName);
        if (!$file->isReadable()) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            FileUtility::getMimeType($file->getPathname()),
            (string)$file->getPathname(),
            false,
            false
        );
    }
}
