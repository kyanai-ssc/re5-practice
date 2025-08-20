<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;

/**
 * PaymentError Entity
 *
 * @property int $id
 * @property string $ip_address
 * @property int|null $error_count
 * @property \Cake\I18n\FrozenTime|null $error_timestamp
 * @property \Cake\I18n\FrozenTime|null $lock_timestamp
 * @property \Cake\I18n\FrozenTime $last_error_timestamp
 * @property int $all_error_count
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class PaymentError extends AppEntity
{
    /**
     * ロックステータス：ロック
     */
    public const TYPE_LOCK = 0;

    /**
     * ロックステータス：ロック解除
     */
    public const TYPE_UNLOCK = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'ip_address' => true,
        'lock_timestamp' => true,
        'last_error_timestamp' => true,
        'all_error_count' => true,
    ];

    /**
     * @inheritDoc
     */
    protected function getLockConfig(): array
    {
        return Configure::readOrFail('Setting.paymentError.lock');
    }

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
     * 決済エラー回数のリセット判定
     *
     * @return bool
     */
    public function shouldResetErrorCount()
    {
        if (!$this->has('error_count') || !$this->has('error_timestamp')) {
            return true;
        }

        $errorTimestamp = DateTimeUtility::convertToDateTimeObject($this->get('error_timestamp'));
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
     * 決済エラー回数をリセット
     *
     * @return void
     */
    public function resetErrorCount()
    {
        $this->set('error_count', null);
        $this->set('error_timestamp', null);
    }

    /**
     * 決済ロックの判定
     *
     * @return bool
     */
    public function shouldLockPayment()
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
     * 決済をロック
     *
     * @return void
     */
    public function lockPayment()
    {
        $this->resetErrorCount();
        $this->set('lock_timestamp', clone $this->commonData()->getNowDateTime());
    }

    /**
     * 決済をアンロック
     *
     * @return void
     */
    public function unlockPayment()
    {
        $this->resetErrorCount();
        $this->set('lock_timestamp', null);
    }

    /**
     * 決済エラー回数をカウントアップ
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
        if (!$this->has('error_timestamp')) {
            $this->set('error_timestamp', clone $this->commonData()->getNowDateTime());
        }

        if ($this->shouldLockPayment()) {
            $this->lockPayment();
        }

        $this->set([
            'all_error_count' => $this->get('all_error_count') + 1,
            'last_error_timestamp' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
        ], ['guard' => false]);
    }
}
