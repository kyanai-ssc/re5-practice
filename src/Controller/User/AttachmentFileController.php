<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\FileTrait;
use App\Controller\UserAppController;
use Cake\Event\EventInterface;

/**
 * AttachmentFile Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class AttachmentFileController extends UserAppController
{
    use FileTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadFileUploadComponent();
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'download',
            'temp',
        ]);

        return $response;
    }

    /**
     * DownloadFile method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function downloadFile()
    {
        return $this->downloadFileAction();
    }

    /**
     * DownloadSavedFile method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function downloadSavedFile()
    {
        return $this->downloadSavedFileAction();
    }
}
