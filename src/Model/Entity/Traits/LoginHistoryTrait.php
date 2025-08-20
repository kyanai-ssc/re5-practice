<?php
declare(strict_types=1);

namespace App\Model\Entity\Traits;

use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * LoginHistory trait.
 */
trait LoginHistoryTrait
{
    /**
     * ロック判定の時間を取得
     *
     * @return int
     */
    public function getLockErrorTime()
    {
        $lockConfig = $this->getLockConfig();
        if (!isset($lockConfig['time'])) {
            throw new CakeException();
        }

        return $lockConfig['time'];
    }

    /**
     * ロック判定の回数を取得
     *
     * @return int
     */
    public function getLockErrorCount()
    {
        $lockConfig = $this->getLockConfig();
        if (!isset($lockConfig['count'])) {
            throw new CakeException();
        }

        return $lockConfig['count'];
    }

    /**
     * パスワード誤り回数のリセット判定
     *
     * @return bool
     */
    public function shouldResetErrorCount()
    {
        if (!$this->has('error_count') || !$this->has('last_error_timestamp')) {
            return true;
        }

        $errorTimestamp = DateTimeUtility::convertToDateTimeObject($this->get('last_error_timestamp'));
        if (!isset($errorTimestamp)) {
            return true;
        }

        $checkTimestamp = new FrozenTime($errorTimestamp->format('Y-m-d H:i:s'));
        $checkTimestamp = $checkTimestamp->addMinutes($this->getLockErrorTime());
        if ($checkTimestamp < $this->commonData()->getNowDateTime()) {
            return true;
        }

        return false;
    }

    /**
     * パスワード誤り回数をリセット
     *
     * @return void
     */
    public function resetErrorCount()
    {
        $this->set('error_count', null);
        $this->set('last_error_timestamp', null);
    }

    /**
     * アカウントロックの判定
     *
     * @return bool
     */
    public function shouldLockAccount()
    {
        if (!$this->has('error_count') || $this->get('error_count') < $this->getLockErrorCount()) {
            return false;
        }

        return true;
    }

    /**
     * ロック状態の判定
     *
     * @return bool
     */
    public function isLock()
    {
        if (!$this->isNew()) {
            if (is_null($this->get('lock_timestamp'))) {
                return false;
            }

            $diff = $this->commonData()->getNowDateTime()->diffInMinutes($this->get('lock_timestamp'));
            if ($diff < $this->getLockErrorTime()) {
                return true;
            }
        }

        return false;
    }

    /**
     * アカウントをロック
     *
     * @return void
     */
    public function lockAccount()
    {
        $this->resetErrorCount();
        $this->set('lock_timestamp', clone $this->commonData()->getNowDateTime());
    }

    /**
     * アカウントをアンロック
     *
     * @return void
     */
    public function unlockAccount()
    {
        $this->resetErrorCount();
        $this->set('lock_timestamp', null);
    }

    /**
     * パスワード誤り回数をカウントアップ
     *
     * @return void
     */
    public function addErrorCount()
    {
        if ($this->shouldResetErrorCount()) {
            $this->resetErrorCount();
        }

        if (!$this->has('error_count')) {
            $this->set('error_count', 0);
        }
        $this->set('error_count', $this->get('error_count') + 1);
        if (!$this->has('last_error_timestamp')) {
            $this->set('last_error_timestamp', clone $this->commonData()->getNowDateTime());
        }

        if ($this->shouldLockAccount()) {
            $this->lockAccount();
        }
    }

    /**
     * ロック設定を取得
     *
     * @return array
     */
    abstract protected function getLockConfig(): array;
}
