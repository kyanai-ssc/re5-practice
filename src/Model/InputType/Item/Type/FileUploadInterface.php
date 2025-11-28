<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * FileUpload interface.
 */
interface FileUploadInterface
{
    /**
     * ファイルのセッション情報を設定
     *
     * @param array|null $fileSession セッション情報
     * @return void
     */
    public function setFileSession(?array $fileSession): void;
}
