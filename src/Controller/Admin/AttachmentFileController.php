<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Controller\Traits\FileTrait;

/**
 * AttachmentFile Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class AttachmentFileController extends AdminAppController
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
