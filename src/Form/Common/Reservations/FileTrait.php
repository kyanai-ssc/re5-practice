<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Model\Entity\FormGroup;
use App\Model\InputType\Item\File;
use App\Model\InputType\Item\Type as ItemType;

/**
 *  FileTrait.
 */
trait FileTrait
{
    /**
     * @var array|null
     */
    protected $attachmentSession = null;

    /**
     * @var array
     */
    protected $fileSession = [];

    /**
     * ファイルのセッションデータを取得
     *
     * @return array
     */
    public function getFileSession()
    {
        return $this->fileSession;
    }

    /**
     * ファイルのセッションデータを設定
     *
     * @param array|null $fileSession セッションデータ
     * @param int $continuousKey 連続予約キー
     * @return void
     */
    public function setFileSession(?array $fileSession, int $continuousKey = 0)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        foreach ($formItemsTable->getFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItem) {
            $inputTypeItem = $formItem->getInputTypeItem();
            if ($inputTypeItem instanceof ItemType\FileUploadInterface) {
                $inputTypeItem->setFileSession($fileSession);
            }
        }

        $this->fileSession[$continuousKey] = $fileSession;
    }

    /**
     * 削除ファイルのセッションデータを設定
     *
     * @param int $key キー
     * @return void
     */
    public function setDeleteFileSession(int $key)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        foreach ($formItemsTable->getFormItems(FormGroup::FORM_TYPE_RESERVATION) as $formItem) {
            $inputTypeItem = $formItem->getInputTypeItem();

            if ($inputTypeItem instanceof File && $inputTypeItem->getFormItem()->get('id') === (int)$key) {
                $inputTypeItem->setDeleteFileSession();
            }
        }
    }
}
