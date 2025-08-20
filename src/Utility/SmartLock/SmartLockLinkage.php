<?php
declare(strict_types=1);

namespace App\Utility\SmartLock;

use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\SmartLock;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Exception;

/**
 * スマートロック連携
 */
class SmartLockLinkage
{
    use LocatorAwareTrait;

    /**
     * スマートロック連携インスタンス
     *
     * @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun | null
     */
    protected $instance;

    /**
     * スマートロックエンティティ
     *
     * @var \App\Model\Entity\SmartLock|null
     */
    protected $smartLock;

    /**
     * Constructor.
     * 設定に応じてリモートロックかアケルンのインスタンスを作成する
     */
    public function __construct()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        if ($systemSettingsTable->getData()->useSmartLock()) {
            /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
            $smartLocksTable = $this->getTableLocator()->get('SmartLocks');
            $smartLock = $smartLocksTable->getData();
            $options = [
                'smartLock' => $smartLock,
            ];
            if ($smartLock->typeIsRemoteLock()) {
                $this->instance = new RemoteLock($options);
            } elseif ($smartLock->typeIsAkerun()) {
                $this->instance = new Akerun($options);
            }
            $this->smartLock = $smartLock;
        }
    }

    /**
     * スマートロック利用か
     *
     * @return bool
     */
    public function useSmartLock(): bool
    {
        return !is_null($this->instance);
    }

    /**
     * リモートロック利用か
     *
     * @return bool
     */
    public function useRemoteLock(): bool
    {
        return $this->instance instanceof RemoteLock;
    }

    /**
     * アケルン利用か
     *
     * @return bool
     */
    public function useAkerun(): bool
    {
        return $this->instance instanceof Akerun;
    }

    /**
     * スマートロックの名前を取得
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        if ($this->useRemoteLock()) {
            $name = Configure::read('Master.smartLock.name.' . SmartLock::TYPE_REMOTE_LOCK);
        }
        if ($this->useAkerun()) {
            $name = Configure::read('Master.smartLock.name.' . SmartLock::TYPE_AKERUN);
        }

        return $name;
    }

    /**
     * 連携するかどうか
     *
     * 予約枠等に連携に必要な設定がされていれば true
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    public function canLinkage(Reservation $reservation): bool
    {
        $instance = $this->getInstance();

        /** @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun $instance */
        return $instance->canLinkage($reservation);
    }

    /**
     * 連携済みかどうか
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     */
    public function isLinked(Reservation $reservation): bool
    {
        $instance = $this->getInstance();

        /** @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun $instance */
        return $instance->isLinked($reservation);
    }

    /**
     * スケジュール登録
     *
     * 予約変更時: 変更前の予約が連携済みで利用時間が終了している場合はスケジュール登録を通るので
     * isLinked は判定しない。必要であれば呼び出し元で制御する。
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     * @throws \Exception
     */
    public function addSchedule(Reservation $reservation): bool
    {
        if ($reservation->isReservationEnded()) {
            return true;
        }

        $reservation->setSmartLockInfo();
        if (!$this->canLinkage($reservation)) {
            return true;
        }

        $instance = $this->getInstance();
        /** @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun $instance */
        $instance->getLock();
        try {
            $result = $instance->addSchedule($reservation);
        } catch (Exception $e) {
            $instance->releaseLock();
            throw $e;
        }
        $instance->releaseLock();

        return $result;
    }

    /**
     * スケジュール削除
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @return bool
     * @throws \Exception
     */
    public function deleteSchedule(Reservation $reservation): bool
    {
        if ($reservation->isReservationEnded()) {
            return true;
        }

        $reservation->setSmartLockInfo();
        if (!$this->isLinked($reservation)) {
            return true;
        }

        $instance = $this->getInstance();
        /** @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun $instance */
        $instance->getLock();
        try {
            $result = $instance->deleteSchedule($reservation);
        } catch (Exception $e) {
            $instance->releaseLock();
            throw $e;
        }
        $instance->releaseLock();

        return $result;
    }

    /**
     * 予約変更時のスマートロック連携
     *
     * 変更前後の予約情報によってスケジュールの 登録/変更/削除 の処理に振り分ける
     *
     * @param \App\Model\Entity\Reservation $reservation 予約情報
     * @param \App\Model\Entity\Reservation $oldReservation 変更前予約情報
     * @return bool
     * @throws \Exception
     */
    public function changeSchedule(Reservation $reservation, Reservation $oldReservation): bool
    {
        $reservation->setSmartLockInfo();
        $oldReservation->setSmartLockInfo();

        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->getTableLocator()->get('ReservationStatuses');
        $statusType =
            $reservationStatusesTable->getReservationStatusType($reservation->get('reservation_status_id'));
        $oldStatusType =
            $reservationStatusesTable->getReservationStatusType($oldReservation->get('reservation_status_id'));
        if ($oldStatusType !== $statusType) {
            if ($statusType === ReservationStatus::STATUS_TYPE_FIXED) {
                // 確定への変更ならスケジュール登録
                return $this->addSchedule($reservation);
            } elseif ($statusType !== ReservationStatus::STATUS_TYPE_VISIT) {
                // 確定/来訪済み以外への変更ならスケジュール削除
                return $this->deleteSchedule($oldReservation);
            }
        } elseif ($statusType === ReservationStatus::STATUS_TYPE_FIXED) {
            // 確定ステータスのままの変更の場合
            if (!$this->isLinked($oldReservation) || $oldReservation->isReservationEnded()) {
                // 未連携ならスケジュール登録
                return $this->addSchedule($reservation);
            }
            if (!$this->canLinkage($reservation) || $reservation->isReservationEnded()) {
                // 変更後の予約枠にデバイスキーの設定が無い、もしくは、変更後の予約が過去の場合はスケジュール削除
                return $this->deleteSchedule($oldReservation);
            }

            $instance = $this->getInstance();
            /** @var \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun $instance */
            $instance->getLock();
            try {
                $result = $instance->changeSchedule($reservation, $oldReservation);
            } catch (Exception $e) {
                $instance->releaseLock();
                throw $e;
            }
            $instance->releaseLock();

            return $result;
        }

        return true;
    }

    /**
     * インスタンス返却
     *
     * インスタンスが無い場合 (スマートロック使用不可の場合に連携処理が呼ばれた場合) は例外をスローする
     *
     * @return \App\Utility\SmartLock\RemoteLock | \App\Utility\SmartLock\Akerun
     */
    protected function getInstance()
    {
        if (!$this->instance) {
            throw new CakeException('not created smart lock instance');
        }

        return $this->instance;
    }

    /**
     * スマートロック設定．事務所IDを取得
     *
     * @return mixed|null
     */
    public function getOrganizationsId()
    {
        if ($this->smartLock === null) {
            return null;
        }

        return $this->smartLock->get('organizations_id');
    }

    /**
     * ユーザー登録
     *
     * @param \App\Model\Entity\User $user 会員情報
     * @return bool
     * @throws \Exception
     */
    public function addUser(User $user): bool
    {
        if (!$this->useAkerun()) {
            return true;
        }

        $instance = $this->getInstance();
        /** @var \App\Utility\SmartLock\Akerun $instance */
        $instance->getLock();
        try {
            $result = (bool)$instance->addUser($user);
        } catch (Exception $e) {
            $instance->releaseLock();
            throw $e;
        }
        $instance->releaseLock();

        return $result;
    }

    /**
     * スマートロック設定.Akerunユーザー名連携する会員項目を取得
     *
     * @return int|null
     */
    public function getFormItemId()
    {
        if ($this->smartLock === null) {
            return null;
        }
        if (!$this->useAkerun()) {
            return null;
        }

        return $this->smartLock->get('form_item_id');
    }

    /**
     * エラーメール送信フラグの変更
     *
     * @param bool $sendFlg エラーメール送信判定フラグ
     * @return void
     */
    public function setErrorMailSendFlg(bool $sendFlg)
    {
        $this->getInstance()->setErrorMailSendFlg($sendFlg);
    }
}
