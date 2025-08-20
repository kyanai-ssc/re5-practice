<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\Files\UploadForm;
use App\Locale\Message;
use App\Utility\FileUtility;
use Cake\Event\EventInterface;
use Cake\Validation\Validation;

/**
 * Files Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class FilesController extends AdminAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('FileUpload');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'upload',
            'uploadEdit',
        ]);

        return $response;
    }

    /**
     * Upload method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function upload()
    {
        $checkResult = false;
        $errorMessages = [];
        $uploadForm = new UploadForm();
        $tmpUpload = [];
        $nextIndex = 0;

        $fileInputs = (array)$this->getRequest()->getData();

        // トークンチェック
        if ($this->TokenValidation->validate($this->FileUpload->getTokenName(), false)) {
            if ($uploadForm->execute($fileInputs)) {
                if (isset($fileInputs['id']) && Validation::notBlank($fileInputs['id'])) {
                    $sessionKeyName = 'fileGroups.edit.' . $fileInputs['id'] . 'files';
                } else {
                    $sessionKeyName = 'fileGroups.add.files';
                }

                $tmpUpload = FileUtility::tmpUploadFile(
                    TMP_UPLOAD_FILES,
                    $fileInputs['file'],
                    $this->FileUpload->get($sessionKeyName, [])
                );

                if ($tmpUpload !== false && is_array($tmpUpload)) {
                    $checkResult = true;
                    $this->FileUpload->add($sessionKeyName, $tmpUpload);
                    $nextIndex = $this->FileUpload->nextIndex($sessionKeyName);
                }
            } else {
                $errorMessages = $uploadForm->getErrors();
            }
        } else {
            $errorMessages['token'] = __(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set(
            [
                'checkResult' => $checkResult,
                'errorMessages' => $errorMessages,
                'file' => $fileInputs,
                'tmpUpload' => $tmpUpload,
                'nextIndex' => $nextIndex,
            ]
        );
    }

    /**
     * Upload edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function uploadEdit()
    {
        $checkResult = false;
        $errorMessages = [];
        $uploadForm = new UploadForm();
        $tmpUpload = false;
        $nextIndex = 0;
        $sessionKeyName = '';
        $fileInputs = (array)$this->getRequest()->getData();

        // トークンチェック
        if ($this->TokenValidation->validate($this->FileUpload->getTokenName(), false)) {
            if ($uploadForm->execute($fileInputs)) {
                if (isset($fileInputs['id']) && Validation::notBlank($fileInputs['id'])) {
                    $sessionKeyName = 'fileGroups.edit.' . $fileInputs['id'] . 'files';
                    $tmpUpload = FileUtility::tmpUploadFile(
                        TMP_UPLOAD_FILES,
                        $fileInputs['file'],
                        $this->FileUpload->get($sessionKeyName, []),
                        $fileInputs['index']
                    );
                }
                if ($tmpUpload !== false && is_array($tmpUpload)) {
                    $checkResult = true;
                    $this->FileUpload->modify($sessionKeyName, $tmpUpload, $fileInputs['index']);
                }
            } else {
                $errorMessages = $uploadForm->getErrors();
            }
        } else {
            $errorMessages['token'] = __(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set(
            [
                'checkResult' => $checkResult,
                'errorMessages' => $errorMessages,
                'file' => $fileInputs,
                'tmpUpload' => $tmpUpload,
                'nextIndex' => $nextIndex,
            ]
        );
    }
}
