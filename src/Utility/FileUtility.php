<?php
declare(strict_types=1);

namespace App\Utility;

use App\Form\Common\Reservations\FileUploadForm;
use App\Model\Entity\Reservation;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\Validation\Validation;
use FilesystemIterator;
use finfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * FileUtility Class.
 */
class FileUtility
{
    public const DIRECTORY_PERMISSION = 0775;
    public const FILE_PERMISSION = 0664;

    /**
     * 一時ファイルを作成
     *
     * @param string $path ディレクトリ
     * @param string $prefix プレフィックス
     * @param int|null $permission パーミッション
     * @return \SplFileInfo
     */
    public static function createTempFile(string $path, string $prefix, ?int $permission = null)
    {
        $file = tempnam($path, $prefix);
        if ($file === false) {
            throw new CakeException();
        }
        if (isset($permission)) {
            static::changePermission($file, $permission);
        }

        return new SplFileInfo($file);
    }

    /**
     * ファイルをコピー
     *
     * @param string $source コピー元
     * @param string $destination コピー先
     * @param int|null $permission パーミッション
     * @return void
     */
    public static function copyFile(string $source, string $destination, ?int $permission = null): void
    {
        if (!copy($source, $destination)) {
            throw new CakeException();
        }
        if (isset($permission)) {
            static::changePermission($destination, $permission);
        }
    }

    /**
     * ファイルを削除
     *
     * @param string $path ファイル
     * @return bool
     */
    public static function deleteFile(string $path): bool
    {
        $fileInfo = new SplFileInfo($path);
        if ($fileInfo->isDir()) {
            throw new CakeException();
        }
        if (!$fileInfo->isFile()) {
            return true;
        }

        return unlink($path);
    }

    /**
     * tmpディレクリにアップロード
     *
     * @param string $path 一時ディレクトリ
     * @param \Psr\Http\Message\UploadedFileInterface|null $fileToUpload アップロードファイル
     * @param array|null $sessionData セッションデータ
     * @param string|null $update 更新キー
     * @return bool|array
     */
    public static function tmpUploadFile(
        $path = TMP_UPLOAD_FILES,
        $fileToUpload = null,
        $sessionData = [],
        $update = null
    ) {
        if (!$fileToUpload) {
            return false;
        }

        $file = static::createTempFile($path, 'tmp', 0666);
        $fileToUpload->moveTo($file->getPathname());

        if (is_string($update) && Validation::notBlank($update)) {
            $sessionData[$update] = [
                'original_file_name' => $fileToUpload->getClientFilename(),
                'file' => $file->getPathname(),
                'fileName' => $file->getFilename(),
                'ext' => static::getFileExtension($fileToUpload->getClientFilename() ?? ''),
                'size' => $file->getSize(),
                'new' => true,
            ];

            $data = $sessionData[$update];
        } else {
            $data = [
                'original_file_name' => $fileToUpload->getClientFilename(),
                'file' => $file->getPathname(),
                'fileName' => $file->getFilename(),
                'ext' => static::getFileExtension($fileToUpload->getClientFilename() ?? ''),
                'size' => $file->getSize(),
                'new' => true,
            ];
            $sessionData[] = $data;
        }

        return $data;
    }

    /**
     * ディレクトリを作成
     *
     * @param string $parent 親ディレクトリ
     * @param array $target 対象ディレクトリ
     * @param int|null $permission パーミッション
     * @return void
     */
    public static function createDirectory(string $parent, array $target, ?int $permission = null): void
    {
        $parentInfo = new SplFileInfo($parent);
        if (!$parentInfo->isDir()) {
            throw new CakeException();
        }

        $path = rtrim($parent, DS);
        foreach ($target as $current) {
            $path .= DS . $current;
            $fileInfo = new SplFileInfo($path);
            if (!$fileInfo->isDir()) {
                if (!mkdir($path)) {
                    throw new CakeException();
                }
                if (isset($permission)) {
                    static::changePermission($path, $permission);
                }
            }
        }
    }

    /**
     * ディレクトリを削除
     *
     * @param string $path ディレクトリ
     * @param bool $pathDelete 指定したディレクトリ自体を削除するか
     * @return void
     */
    public static function deleteDirectory($path, $pathDelete = true): void
    {
        $parent = new SplFileInfo($path);
        if (!$parent->isDir()) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                if (!rmdir($fileInfo->getPathname())) {
                    throw new CakeException();
                }
            } else {
                if (!unlink($fileInfo->getPathname())) {
                    throw new CakeException();
                }
            }
        }

        if ($pathDelete) {
            if (!rmdir($path)) {
                throw new CakeException();
            }
        }
    }

    /**
     * tempディレクトリファイルの削除
     *
     * @param string $path ディレクリパス
     * @param \Cake\I18n\FrozenTime $deleteTime 削除日時
     * @return void
     */
    public static function tmpFileDeleteOverTime(string $path, FrozenTime $deleteTime)
    {
        $directory = new SplFileInfo($path);
        if (!$directory->isDir() || !$directory->isReadable()) {
            throw new CakeException();
        }

        foreach (new FilesystemIterator($directory->getPathname()) as $fileInfo) {
            if (!($fileInfo instanceof SplFileInfo)) {
                throw new CakeException();
            }
            if ($fileInfo->isFile() && $fileInfo->getMTime() < $deleteTime->getTimestamp()) {
                unlink($fileInfo->getPathname());
            }
        }
    }

    /**
     * バイト数を各単位に変換
     *
     * @param mixed $byte バイト数
     * @param string $unit 単位
     * @param int|null $rounding 小数点以下切り上げ
     * @return float|int
     */
    public static function getFileSize($byte, $unit = 'gb', $rounding = null)
    {
        $size = 0;
        if (!is_array($byte)) {
            switch ($unit) {
                case 'gb':
                    $size = (float)$byte / 1024 / 1024 / 1024;
                    break;
                case 'mb':
                    $size = (float)$byte / 1024 / 1024;
                    break;
                case 'kb':
                    $size = (float)$byte / 1024;
                    break;
                default:
                    $size = (float)$byte;
                    break;
            }
        }

        if ($rounding !== null && is_numeric($rounding)) {
            $size = round($size, $rounding);
        }

        return $size;
    }

    /**
     * ディレクトリをコピー
     *
     * @param string $source コピー元
     * @param string $destination コピー先
     * @param int $directoryPermission ディレクトリのパーミッション
     * @param int $filePermission ファイルのパーミッション
     * @return void
     */
    public static function copyDirectory(
        string $source,
        string $destination,
        ?int $directoryPermission = null,
        ?int $filePermission = null
    ) {
        if (!isset($directoryPermission)) {
            $directoryPermission = static::DIRECTORY_PERMISSION;
        }
        if (!isset($filePermission)) {
            $filePermission = static::FILE_PERMISSION;
        }

        $copy = function ($source, $destination) use (&$copy, $directoryPermission, $filePermission) {
            $source = new SplFileInfo($source);
            $destination = new SplFileInfo($destination);

            if (!$destination->isDir()) {
                if (!mkdir($destination->getPathname())) {
                    throw new CakeException();
                }
                if (!chmod($destination->getPathname(), $directoryPermission)) {
                    throw new CakeException();
                }
            }

            $iterator = new FilesystemIterator($source->getPathname());
            foreach ($iterator as $fileInfo) {
                if (!($fileInfo instanceof SplFileInfo)) {
                    throw new CakeException();
                }

                $target = new SplFileInfo($destination . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
                if ($fileInfo->isDir()) {
                    call_user_func($copy, $fileInfo->getPathname(), $target->getPathname());
                } else {
                    if (!copy($fileInfo->getPathname(), $target->getPathname())) {
                        throw new CakeException();
                    }
                    if (!chmod($target->getPathname(), $filePermission)) {
                        throw new CakeException();
                    }
                }
            }
        };
        call_user_func($copy, $source, $destination);
    }

    /**
     * パーミッションの変更
     *
     * @param string $path 変更対象
     * @param int $permission パーミッション
     * @return void
     */
    public static function changePermission(string $path, int $permission)
    {
        if (!function_exists('posix_geteuid')) {
            return;
        }

        $fileInfo = new SplFileInfo($path);
        if ($fileInfo->getOwner() !== posix_geteuid()) {
            if ((fileperms($path) & $permission) !== $permission) {
                throw new CakeException();
            }

            return;
        }

        if (!chmod($path, $permission)) {
            throw new CakeException();
        }
    }

    /**
     * パーミッションを再帰的に変更
     *
     * @param string $path 対象のパス
     * @param int $directoryPermission ディレクトリのパーミッション
     * @param int $filePermission ファイルのパーミッション
     * @return void
     */
    public static function changePermissionRecursive(string $path, int $directoryPermission, int $filePermission): void
    {
        $target = new SplFileInfo($path);
        if (!$target->isDir()) {
            static::changePermission($target->getPathname(), $filePermission);

            return;
        }
        static::changePermission($target->getPathname(), $directoryPermission);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $target->getPathname(),
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                $permission = $directoryPermission;
            } else {
                $permission = $filePermission;
            }
            static::changePermission($fileInfo->getPathname(), $permission);
        }
    }

    /**
     * 拡張子を取得
     *
     * @param string $path ファイル
     * @return string
     */
    public static function getFileExtension(string $path)
    {
        $fileInfo = new SplFileInfo($path);

        return strtolower($fileInfo->getExtension());
    }

    /**
     * MIMEタイプを取得
     *
     * @param string $path ファイル
     * @return string
     */
    public static function getMimeType(string $path): string
    {
        $finfo = new finfo(FILEINFO_MIME);
        $data = $finfo->file($path);
        if ($data === false) {
            throw new CakeException();
        }
        $data = explode(';', $data);
        $data = reset($data);

        return $data;
    }

    /**
     * パターンに合致するファイルを再帰的に削除
     *
     * @param string $directory ディレクトリ
     * @param string $pattern パターン
     * @return void
     */
    public static function deleteByName(string $directory, string $pattern)
    {
        $delete = function ($directory, $pattern) use (&$delete) {
            $directory = new SplFileInfo($directory);

            $iterator = new FilesystemIterator($directory->getPathname());
            foreach ($iterator as $fileInfo) {
                if (!($fileInfo instanceof SplFileInfo)) {
                    throw new CakeException();
                }

                if ($fileInfo->isDir()) {
                    call_user_func($delete, $fileInfo->getPathname(), $pattern);
                } else {
                    if (preg_match($pattern, $fileInfo->getBasename()) === 1) {
                        if (!unlink($fileInfo->getPathname())) {
                            throw new CakeException();
                        }
                    }
                }
            }
        };
        call_user_func($delete, $directory, $pattern);
    }

    /**
     * 予約時のファイル項目の一時ファイルを作成
     *
     * @param string $path ディレクトリ
     * @param string $prefix プレフィックス
     * @param \Psr\Http\Message\UploadedFileInterface $fileToUpload ファイル情報
     * @param int|null $permission パーミッション
     * @return \SplFileInfo
     */
    public static function createReservationTmpFile(
        string $path,
        string $prefix,
        $fileToUpload,
        ?int $permission = null,
    ) {
        $name = $fileToUpload->getClientFilename();
        if (!isset($name)) {
            throw new CakeException();
        }
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $randomName = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
        $tmpSavePath = $path . DS . $randomName;

        if (isset($permission)) {
            static::changePermission($tmpSavePath, $permission);
        }

        return new SplFileInfo($tmpSavePath);
    }

    /**
     * tmpディレクリに予約時のファイルをアップロード
     *
     * @param string $path 一時ディレクトリ
     * @param \Psr\Http\Message\UploadedFileInterface|null $fileToUpload アップロードファイル
     * @return bool|array
     */
    public static function tmpUploadReservationFile(
        string $path = TMP_UPLOAD_RESERVATION_FILE,
        $fileToUpload = null,
    ) {
        if (!$fileToUpload) {
            return false;
        }

        $sessionData = [];

        $file = static::createReservationTmpFile($path, '', $fileToUpload, 0666);
        $fileToUpload->moveTo($file->getPathname());

        $data = [
            'original_file_name' => $fileToUpload->getClientFilename(),
            'file' => $file->getPathname(),
            'fileName' => $file->getFilename(),
            'ext' => static::getFileExtension($fileToUpload->getClientFilename() ?? ''),
            'size' => $file->getSize(),
            'new' => true,
        ];
        $sessionData[] = $data;

        return $data;
    }

    /**
     * アップロードファイルのディレクトリ作成とアップロード
     *
     * @param \App\Model\Entity\Reservation $entity エンティティー
     * @param int $formItemId フォーム項目ID
     * @param string $path パス
     * @param array $fileToUpload ファイル
     * @return bool
     */
    public static function createAndUploadReservationFile(
        Reservation $entity,
        int $formItemId,
        string $path = UPLOAD_RESERVATION_FILE,
        ?array $fileToUpload = null
    ) {
        if (!$fileToUpload) {
            return false;
        }

        $file = static::createUploadFileDirectoryAndPath(
            $path,
            $entity,
            $formItemId,
            $fileToUpload,
            static::FILE_PERMISSION,
        );
        if (file_exists($fileToUpload['file'])) {
            if (copy($fileToUpload['file'], $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * アップロードファイルを保存するディレクトリを作成し、アップロードファイルのパスを返す
     *
     * @param string $path パス
     * @param \App\Model\Entity\Reservation $entity エンティティー
     * @param int $formItemId フォーム項目ID
     * @param array $fileToUpload ファイル情報
     * @param int|null $permission パーミッション
     * @return string $savePath アップロードファイルを保存するパス
     */
    public static function createUploadFileDirectoryAndPath(
        string $path,
        Reservation $entity,
        int $formItemId,
        array $fileToUpload,
        ?int $permission = null,
    ) {
        $reservationId = $entity->get('id');
        $ext = pathinfo($fileToUpload['original_file_name'], PATHINFO_EXTENSION);

        // 予約枠idの数値によってフォルダーの値を取得
        $fileNumber = intdiv($reservationId, Configure::readOrFail('Setting.file.separateDirectoryNumber')) + 1;

        $directoryPath = $path . DS . $fileNumber . DS . $reservationId . DS . $formItemId;

        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, static::DIRECTORY_PERMISSION, true);
            if (isset($permission)) {
                static::changePermission($path, $permission);
            }
        }

        // パスの作成
        $fileName = 'form_upload' . '.' . $ext;
        $savePath = $directoryPath . DS . $fileName;

        if (isset($permission)) {
            static::changePermission($savePath, $permission);
        }

        return $savePath;
    }

    /**
     * ファイルをディレクトリに保存
     *
     * @param array $file 連続予約データ
     * @param \App\Model\Entity\Reservation $entity エンティティー
     * @param int $formItemId フォーム項目ID
     * @return \Cake\Http\Response|null|void
     */
    public static function uploadReservationFile(array $file, Reservation $entity, int $formItemId)
    {
        $uploadForm = new FileUploadForm();
        $tmpUpload = [];

        if ($uploadForm->execute($file)) {
            $tmpUpload = FileUtility::createAndUploadReservationFile(
                $entity,
                $formItemId,
                UPLOAD_RESERVATION_FILE,
                $file
            );

            if (!$tmpUpload) {
                throw new CakeException();
            }
        } else {
            throw new CakeException();
        }
    }
}
