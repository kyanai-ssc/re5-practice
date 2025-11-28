<?php
declare(strict_types=1);

namespace App\Model\Table\Traits;

use Cake\Core\Configure;

/**
 * FileUpload trait.
 */
trait FileUploadTrait
{
    /**
     * @var array
     */
    protected $uploadFilePaths = [];

    /**
     * フォームのアップロードディレクトリを取得
     *
     * @param int $reservationId データID
     * @param string $formItemId フォーム項目ID
     * @return array
     */
    public function getFormUploadDirectory($reservationId, $formItemId)
    {
        $split = Configure::readOrFail('Setting.file.separateDirectoryNumber');

        return [
            (string)(($reservationId - ($reservationId % $split)) / $split + 1),
            (string)$reservationId,
            (string)$formItemId,
        ];
    }
}
