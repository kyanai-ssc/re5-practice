<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Form\Common\Reservations\FileUploadForm;
use App\Form\Common\Reservations\ReservationForm;
use App\Locale\Message;
use App\Model\Entity\FormItem;
use App\Model\Entity\Reservation;
use App\Model\InputType\Item\Type\FileUploadTrait;
use App\Utility\FileUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;
use SplFileInfo;

/**
 * File trait.
 */
trait FileTrait
{
    use FileUploadTrait;

    /**
     * @var string
     */
    protected $attachmentTokenKeyAdd = 'reservations_add';

    /**
     * @var string
     */
    protected $attachmentTokenKeyEdit = 'reservations_edit_%s';

    /**
     * FileUpload コンポーネントの読み込み
     *
     * @return void
     */
    protected function loadFileUploadComponent()
    {
        $this->loadComponent('FileUpload');
    }

    /**
     * アップロードのフォーム項目を取得
     *
     * @return \App\Model\Entity\FormItem
     */
    protected function getFormItemForUpload()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItemId = $this->getRequest()->getQuery('form_item_id');
        if (
            !is_scalar($formItemId) || !$formItemsTable->validatePrimaryKey($formItemId)
        ) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $formItem = $formItemsTable->getFormItem((int)$formItemId);
        if (!isset($formItem)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        return $formItem;
    }

    /**
     * アップロードの対象予約データを取得
     *
     * @return \App\Model\Entity\Reservation|null
     */
    protected function getReservationForUpload()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        $reservationId = Hash::get((array)$this->getRequest()->getQuery(), 'id');
        $reservation = null;
        if (isset($reservationId)) {
            if (!$reservationsTable->validatePrimaryKey($reservationId)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
            $reservation = $reservationsTable->get($reservationId);
        }

        return $reservation;
    }

    /**
     * ファイル項目IDからReservationAdditionエンティティを取得
     *
     * @param string $formItemId ファイル項目ID
     * @return \App\Model\Entity\ReservationAddition|null
     */
    protected function getReservationForDownload(string $formItemId)
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        /** @var \App\Model\Table\ReservationAdditionsTable $reservationAdditionsTable */
        $reservationAdditionsTable = $this->getTableLocator()->get('ReservationAdditions');

        $reservationId = Hash::get((array)$this->getRequest()->getQuery(), 'id');
        $reservation = null;
        if (isset($reservationId)) {
            if (!$reservationsTable->validatePrimaryKey($reservationId)) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
            /** @var \App\Model\Entity\ReservationAddition $reservation */
            $reservation = $reservationAdditionsTable->find('file', [
                'reservation_id' => $reservationId,
                'form_item_id' => $formItemId,
            ])->first();
        }

        return $reservation;
    }

    /**
     * 添付ファイルアップロード処理
     *
     * @return void
     */
    public function uploadFileAction()
    {
        $errorMessages = [];
        $uploadForm = new FileUploadForm();
        $tmpUpload = [];
        $fileData = [];
        $index = 'undefined';

        $reservation = $this->getReservationForUpload();
        $reservationId = Hash::get((array)$this->getRequest()->getQuery(), 'id');

        // トークンチェック
        if (!isset($reservationId)) {
            $this->FileUpload->setTokenName($this->attachmentTokenKeyAdd);
        } else {
            $this->FileUpload->setTokenName(sprintf($this->attachmentTokenKeyEdit, $reservationId));
        }
        if (!$this->TokenValidation->validate($this->FileUpload->getTokenName(), false)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $fileInputs = (array)$this->getRequest()->getData();
        $formItem = $this->getFormItemEntity($fileInputs['form_item_id']);
        $uploadForm->setFormItem($formItem);
        $uploadForm->setReservationEntity($reservation);
        if ($uploadForm->execute($fileInputs)) {
            $tmpDir = TMP_UPLOAD_RESERVATION_FILE;
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            if ($reservationId || !$this->request->getSession()->read('reservations.add.continuousData')) {
                $index = Configure::readOrFail('Setting.file.defaultIndex');
            } elseif (
                $this->request->getData('continuous_key') === 'undefined'
            ) {
                $keys = array_keys($this->request->getSession()->read('reservations.add.continuousData'));
                $index = (int)max($keys ?: [0]) + 1;
            } else {
                $index = $this->request->getData('continuous_key');
            }

            if (!isset($reservationId)) {
                $sessionKeyName = 'file.tmp.add.' . $index . '.' . $fileInputs['form_item_id'];
            } else {
                $sessionKeyName = 'file.tmp.edit.' . $index . '.' . $fileInputs['form_item_id'];
            }

            $tmpUpload = FileUtility::tmpUploadReservationFile(
                TMP_UPLOAD_RESERVATION_FILE,
                $fileInputs['file'],
            );

            if ($tmpUpload !== false && is_array($tmpUpload)) {
                // セッションに保存してあるファイル情報を更新または追加する
                $this->FileUpload->addTmpFileSession($sessionKeyName, $tmpUpload);
                if (!isset($reservationId)) {
                    $this->request->getSession()->delete(
                        'file.delete.add.' . $index . '.' . $fileInputs['form_item_id']
                    );
                } else {
                    $this->request->getSession()->delete(
                        'file.delete.edit.' . $index . '.' . $fileInputs['form_item_id']
                    );
                }
                $fileData[$fileInputs['form_item_id']] = $tmpUpload;
            }
        } else {
            $errorMessages = $uploadForm->getErrors();
        }

        // 画面表示用にファイル情報をセット
        $uploadForm->setFileSession($fileData);

        $this->set(
            [
                'errorMessages' => $errorMessages,
                'file' => $fileInputs,
                'tmpUpload' => $tmpUpload,
                'index' => $index,
                'uploadForm' => $uploadForm,
            ]
        );
    }

    /**
     * 予約編集時、保存されているファイルを削除するためのセッション追加と表示変更
     *
     * @return void
     */
    public function deleteSavedFileAction()
    {
        $this->getRequest()->allowMethod('post');

        $formItem = $this->getFormItemForUpload();
        $reservation = $this->getReservationForUpload();
        $reservationId = Hash::get((array)$this->getRequest()->getQuery(), 'id');

        // トークンチェック
        if (!isset($reservationId)) {
            $this->FileUpload->setTokenName($this->attachmentTokenKeyAdd);
        } else {
            $this->FileUpload->setTokenName(sprintf($this->attachmentTokenKeyEdit, $reservationId));
        }
        if (!$this->TokenValidation->validate($this->FileUpload->getTokenName(), false)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $index = Configure::readOrFail('Setting.file.defaultIndex');
        $formItemId = $this->request->getData('form_item_id');

        $this->request->getSession()->write('file.delete.edit.' . $index . '.' . $formItemId, $formItemId);
        // 表示用のセッションを削除
        $uploadForm = new FileUploadForm();
        $formItem = $this->getFormItemEntity($formItemId);
        $uploadForm->setFormItem($formItem);
        $uploadForm->setReservationEntity($reservation);

        $this->set([
            'uploadForm' => $uploadForm,
            'index' => $index,
        ]);
    }

    /**
     * 一時アップロードした添付ファイルの削除処理
     *
     * @return void
     */
    public function deleteTmpFileAction()
    {
        $this->getRequest()->allowMethod('post');

        $formItem = $this->getFormItemForUpload();
        $reservation = $this->getReservationForUpload();
        $reservationId = Hash::get((array)$this->getRequest()->getQuery(), 'id');

        // トークンチェック
        if (!isset($reservationId)) {
            $this->FileUpload->setTokenName($this->attachmentTokenKeyAdd);
        } else {
            $this->FileUpload->setTokenName(sprintf($this->attachmentTokenKeyEdit, $reservationId));
        }
        if (!$this->TokenValidation->validate($this->FileUpload->getTokenName(), false)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($reservationId || !$this->request->getSession()->read('reservations.add.continuousData')) {
            $index = Configure::readOrFail('Setting.file.defaultIndex');
        } elseif (
            $this->request->getData('continuous_key') === 'undefined'
        ) {
            $keys = array_keys($this->request->getSession()->read('reservations.add.continuousData'));
            $index = (int)max($keys ?: [0]) + 1;
        } else {
            $index = $this->request->getData('continuous_key');
        }

        // 一時アップロードにあげた画像をフォルダから削除
        $file = $this->request->getSession()->read('file');
        $formItemId = $this->request->getData('form_item_id');

        $tmpFilePath = $file['tmp']['add'][$index][$formItemId]['file'] ?? null;
        if (!isset($reservationId)) {
            $tmpFilePath = $file['tmp']['add'][$index][$formItemId]['file'] ?? null;
            $filePath = $file['add'][$index][$formItemId]['file'] ?? null;
        } else {
            $tmpFilePath = $file['tmp']['edit'][$index][$formItemId]['file'] ?? null;
            $filePath = $file['edit'][$index][$formItemId]['file'] ?? null;
        }

        if (!$tmpFilePath && !$filePath) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if (isset($tmpFilePath) && file_exists($tmpFilePath)) {
            if (unlink($tmpFilePath)) {
                // 一時アップロードのセッションを削除
                if (!$reservationId) {
                    $this->request->getSession()->delete('file.tmp.add.' . $index . '.' . $formItemId);
                } else {
                    $this->request->getSession()->delete('file.tmp.edit.' . $index . '.' . $formItemId);
                }
                // 表示用のセッションを削除
                $uploadForm = new FileUploadForm();
                $formItem = $this->getFormItemEntity($formItemId);
                $uploadForm->setFormItem($formItem);
                $uploadForm->setReservationEntity($reservation);
            } else {
                throw new CakeException(Message::ERROR_DELETE_FILE);
            }
        } elseif (isset($filePath) && file_exists($filePath)) {
            if ($reservationId) {
                $this->request->getSession()->write('file.delete.edit.' . $index . '.' . $formItemId, $filePath);
            } else {
                $this->request->getSession()->write('file.delete.add.' . $index . '.' . $formItemId, $filePath);
            }

            // 表示用のセッションを削除
            $uploadForm = new FileUploadForm();
            $formItem = $this->getFormItemEntity($formItemId);
            $uploadForm->setFormItem($formItem);
            $uploadForm->setReservationEntity($reservation);
        } else {
            throw new CakeException(Message::ERROR_DELETE_FILE);
        }

        $this->set([
            'uploadForm' => $uploadForm,
            'index' => $index,
        ]);
    }

    /**
     * 一時アップロードファイルのダウンロード
     *
     * @return \Cake\Http\Response|null|void
     */
    public function downloadFileAction()
    {
        $fileData = $this->request->getQuery('file');

        if (!is_array($fileData) || empty($fileData['fileName'])) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $fileName = $fileData['fileName'];

        $file = new SplFileInfo(TMP_UPLOAD_RESERVATION_FILE . DS . $fileName);

        if (!$file->isFile()) {
            throw new NotFoundException();
        }

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            FileUtility::getMimeType($file->getPathname()),
            (string)$file->getPathname(),
        );
    }

    /**
     * 保存されているファイルのダウンロード
     *
     * @return \Cake\Http\Response|null|void
     */
    protected function downloadSavedFileAction()
    {
        $formItemId = $this->getRequest()->getQuery('form_item_id');
        if (!is_string($formItemId)) {
            throw new NotFoundException();
        }
        $reservation = $this->getReservationForDownload($formItemId);

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->getTableLocator()->get('ReservationGuestCodes');

        // ファイル取得
        if (!isset($reservation)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
        if ($this->commonData()->existsUserLoginData()) {
            // データの会員IDが一致しなければエラー
            $userId = $this->commonData()->getUserLoginData()->get('id');
            if ($reservation instanceof Reservation && (string)$reservation->get('user_id') !== (string)$userId) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
        } elseif (!$this->commonData()->existsAdminLoginData()) {
            $guestCode = $this->getRequest()->getSession()->read('guest.code');
            if (!$reservationGuestCodesTable->existsReId($reservation->get('id'), $guestCode)) {
                throw new NotFoundException();
            }
        }

        // ファイル名・ファイルパス・ファイルタイプを取得
        $reservationId = $reservation->get('reservation_id');
        $formItemId = $reservation->get('form_item_id');
        $downloadFileName = $reservation->get('value');

        if (!$reservationId || !$formItemId || !$downloadFileName) {
            throw new NotFoundException();
        }
        $fileName = 'form_upload';
        if ((int)$reservationId < Configure::readOrFail('Setting.file.separateDirectoryNumber')) {
            $dir = Configure::readOrFail('Setting.file.firstFileDirectory');
        } else {
            $dir = (int)$reservationId % Configure::readOrFail('Setting.file.separateDirectoryNumber') + 1;
        }
        $path = UPLOAD_RESERVATION_FILE . DS . $dir . DS . $reservationId . DS . $formItemId . DS . $fileName;

        $savedFile = glob($path . '.*');

        if ($savedFile) {
            $savedFilPath = $savedFile[0];

            $file = new SplFileInfo($savedFilPath);

            if (!$file->isFile()) {
                throw new NotFoundException();
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                throw new CakeException();
            }
            $mime = finfo_file($finfo, $savedFilPath);
            if ($mime === false) {
                throw new CakeException();
            }
            finfo_close($finfo);

            // ダウンロード処理
            $response = $this->FileDownload->setDownloadResponse(
                $downloadFileName,
                $mime,
                $savedFilPath
            );

            return $response;
        }

        throw new NotFoundException();
    }

    /**
     * ファイルのフォーム項目のエンティティを取得
     *
     * @param string $formItemId フォーム項目ID
     * @return \App\Model\Entity\FormItem
     */
    protected function getFormItemEntity(string $formItemId): FormItem
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItem = $formItemsTable->getFormItem((int)$formItemId);
        if (!isset($formItem)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        return $formItem;
    }

    /**
     * 予約登録のファイルセッションキーを取得
     *
     * @param int $index インデックス
     * @return string セッションキー
     */
    protected function makeFileSessionKey(int $index)
    {
        return 'file.add.' . $index;
    }

    /**
     * 予約編集のファイルセッションキーを取得
     *
     * @param int $index インデックス
     * @return string セッションキー
     */
    protected function makeEditFileSessionKey(int $index)
    {
        return 'file.edit.' . $index;
    }

    /**
     * @var array
     */
    protected $fileSession = [];

    /**
     * 予約登録後に一時ファイルをクリアする
     *
     * @param array $continuousData 連続予約データ
     * @return void
     */
    protected function afterSaveTmpFileDelete(array $continuousData = [])
    {
        $files = $this->request->getSession()->read('file.add');
        foreach ($files as $key => $file) {
            if (!isset($continuousData[$key])) {
                $this->FileUpload->deleteTempFiles($file);
            }
            $this->request->getSession()->delete('file.add.' . $key);
        }
    }

    /**
     * 一時ファイルを削除する
     *
     * @return void
     */
    public function removeAttachTmpFile()
    {
        $continuousData = (array)$this->getRequest()->getSession()->read(
            'reservations.add.continuousData'
        );
        $allFileSession = (array)$this->getRequest()->getSession()->read(
            'file.add'
        );
        if (!empty($allFileSession)) {
            foreach ($allFileSession as $key => $files) {
                if (isset($continuousData[$key])) {
                    $this->FileUpload->deleteTempFiles($files);
                }
            }
        }
    }

    /**
     * 入力によるファイルセッションをセット
     *
     * @param int $index インデックス
     * @return void
     */
    public function makeNewfileSession(int $index)
    {
        $fileData = $this->request->getSession()->read('file.add.' . $index);

        // アップロードファイルと削除ファイルを反映(削除ファイルに関しては一時アップロードディレクトリから削除)
        $tmpFile = $this->request->getSession()->read('file.tmp.add.' . $index);
        $deleteFile = $this->request->getSession()->read('file.delete.add.' . $index);

        if ($deleteFile) {
            foreach ($deleteFile as $key => $tmpFilePath) {
                $this->request->getSession()->delete('file.add.' . $index . '.' . $key);
                FileUtility::deleteFile($tmpFilePath);
            }
            $this->request->getSession()->delete('file.delete.add.' . $index);
        }

        if ($tmpFile) {
            // ファイルセッションに入力画面で選択した画像を追加
            if ($fileData) {
                $this->request->getSession()->write('file.add.' . $index, array_replace($fileData, $tmpFile));
            } else {
                $this->request->getSession()->write('file.add.' . $index, $tmpFile);
            }
            $this->request->getSession()->delete('file.tmp.add.' . $index);
        }
    }

    /**
     * 入力によるファイルセッションとファイル項目をセット
     *
     * @param \App\Form\Common\Reservations\ReservationForm $reservationForm 予約フォーム
     * @param int $index インデックス
     * @return array ファイル項目の入力
     */
    public function makeNewfileSessionAndReservationInputs(ReservationForm $reservationForm, int $index)
    {
        $reservationInputs['reservations']['addition_values'] = [];

        // 編集前に保存されていた値をエンティティにセット
        $reservation_additions = $reservationForm->getReservationEntity()->get('reservation_additions');
        foreach ($reservation_additions as $entity) {
            $formItemId = $entity->get('form_item_id');
            $value = $entity->get('value');

            /** @var \App\Model\Table\FormItemsTable $formItemsTable */
            $formItemsTable = $this->getTableLocator()->get('FormItems');

            $formItem = $formItemsTable->getFormItem($formItemId);
            if ($formItem !== null && (int)$formItem->input_type === FormItem::INPUT_TYPE_FILE && isset($value)) {
                $reservationInputs['reservations']['addition_values']['item_' . $formItemId] = $value;
            }
        }

        //アップロードファイルと削除ファイルを反映(削除ファイルに関しては一時アップロードディレクトリから削除)
        $tmpFile = $this->request->getSession()->read('file.tmp.edit.' . $index);
        $deleteFile = $this->request->getSession()->read('file.delete.edit.' . $index);
        if ($deleteFile) {
            foreach ($deleteFile as $key => $tmpFilePath) {
                $this->request->getSession()->delete('file.edit.' . $index . '.' . $key);
                // FileUtility::deleteFile($tmpFilePath);
                $reservationForm->setDeleteFileSession($key);
                $reservationInputs['reservations']['addition_values']['item_' . $key] = '';
            }
        }

        if ($tmpFile) {
            $this->request->getSession()->write('file.edit.' . $index, $tmpFile);
            $this->request->getSession()->delete('file.tmp.edit.' . $index);
        }

        return $reservationInputs['reservations']['addition_values'];
    }
}
