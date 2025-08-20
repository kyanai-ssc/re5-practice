<?php
declare(strict_types=1);

namespace App\Model;

/**
 * ImportableTable Interface.
 */
interface ImportableTableInterface
{
    public const IMPORT_LOCK_HOLIDAY = 1;
    public const IMPORT_LOCK_EVENT = 2;
    public const IMPORT_LOCK_USER = 3;
    public const IMPORT_LOCK_RESERVATION = 4;

    /**
     * インポート処理のロックを試行
     *
     * @return bool
     */
    public function tryLockForImport();

    /**
     * インポート処理のロックを取得
     *
     * @return void
     */
    public function getLockForImport();

    /**
     * インポート処理のロックを解放
     *
     * @return void
     */
    public function releaseLockForImport();
}
