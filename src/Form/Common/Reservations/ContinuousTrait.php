<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * Continuous trait.
 */
trait ContinuousTrait
{
    /**
     * @var array|null
     */
    protected $continuousParameter = null;

    /**
     * @var array
     */
    protected $reservationForms = null;

    /**
     * 連続予約のパラメータを取得
     *
     * @param string|null $key キー
     * @return mixed パラメータ
     */
    public function getContinuousParameter(?string $key = null)
    {
        if (!isset($this->continuousParameter)) {
            return null;
        }
        if (!isset($key)) {
            return $this->continuousParameter;
        }

        return Hash::get($this->continuousParameter, $key);
    }

    /**
     * 連続予約のパラメータを設定
     *
     * @param array $continuousParameter パラメータ
     * @return void
     */
    public function setContinuousParameter(array $continuousParameter)
    {
        $this->continuousParameter = $continuousParameter;
    }

    /**
     * 連続予約のパラメータを検証
     *
     * @param bool $isBackTransition 戻るボタンの判定
     * @return bool 検証結果
     */
    public function validateContinuousParameter(bool $isBackTransition = false)
    {
        // 連続予約キー
        $continuousKey = $this->getContinuousParameter('key');
        if (isset($continuousKey)) {
            if (!is_scalar($continuousKey) || (string)$continuousKey === '') {
                return false;
            }
            $continuousKey = (string)$continuousKey;
        } else {
            $continuousKey = null;
        }

        // 連続予約パラメータ
        $continuousParameter = $this->getContinuousParameter('parameter');
        if (($isBackTransition || $this->isConfirm() && empty($continuousParameter)) && !isset($continuousKey)) {
            return false;
        }

        // 連続予約データ
        $continuousData = (array)$this->getContinuousParameter('data');
        if (isset($continuousKey) && !isset($continuousData[$continuousKey])) {
            return false;
        }
        if (!$isBackTransition && !$this->isConfirm() && count($continuousData) >= $this->getContinuousLimit()) {
            return false;
        }

        // 入力画面
        if (!$this->isConfirm()) {
            if (empty($continuousParameter)) {
                // 続けて予約を利用中ではない場合は、続けて予約のデータを空にする
                $continuousData = [];
            } else {
                // 続けて予約を利用中の場合は、現在操作中の予約を除外する
                unset($continuousData[$continuousKey]);
            }
            $this->continuousParameter['data'] = $continuousData;
        }

        // 予約フォーム生成
        $reservationForms = [];
        foreach ($continuousData as $key => $data) {
            $reservationForms[$key] = $this->createReservationForm();
            $reservationForms[$key]->setConfirm($this->isConfirm());
            $reservationForms[$key]->setContinuousParameter([
                'key' => $key,
            ] + (array)$this->getContinuousParameter());
            $reservationForms[$key]->setReservationParameter(Hash::get($data, 'parameter', []));

            $otherForms = $reservationForms;
            unset($otherForms[$key]);
            $reservationForms[$key]->setReservationForms($otherForms);

            if (!$reservationForms[$key]->validateReservationParameter()) {
                return false;
            }
            $reservationForms[$key]->initializeReservationEntity(Hash::get($data, 'data', []));
        }
        $this->setReservationForms($reservationForms);

        return true;
    }

    /**
     * 連続予約を判定
     *
     * @return bool 判定結果
     */
    public function isContinuous()
    {
        if (empty($this->getContinuousParameter('parameter'))) {
            return false;
        }

        return true;
    }

    /**
     * 予約フォームを取得
     *
     * @param int $key キー
     * @return array|\App\Form\AppForm|null 予約フォーム
     */
    public function getReservationForm(?int $key = null)
    {
        if (!isset($key)) {
            return $this->reservationForms;
        }

        return Hash::get($this->reservationForms, (string)$key);
    }

    /**
     * 予約フォームを設定
     *
     * @param array $reservationForms 予約フォーム
     * @return void
     */
    public function setReservationForms(array $reservationForms)
    {
        $this->reservationForms = $reservationForms;
    }

    /**
     * 予約のエンティティーを取得
     *
     * @return \App\Model\Entity\Reservation[] エンティティー
     */
    public function getReservationEntities()
    {
        $entities = [];
        foreach ((array)$this->getReservationForm() as $index => $reservationForm) {
            $entities[$index] = $reservationForm->getReservationEntity();
        }

        return $entities;
    }

    /**
     * 連続予約キーを初期化
     *
     * @return void
     */
    public function clearContinuousKey()
    {
        $continuousParameter = $this->getContinuousParameter();
        unset($continuousParameter['key']);
        $this->setContinuousParameter($continuousParameter);
    }

    /**
     * 次の連続予約キーを生成
     *
     * @return void
     */
    protected function generateContinuousKey()
    {
        $continuousParameter = $this->getContinuousParameter();
        if (!isset($continuousParameter['key'])) {
            $continuousParameter['key'] = 0;
            if (!empty($continuousParameter['data'])) {
                $continuousParameter['key']
                    = (int)ArrayUtility::arrayMax(array_keys($continuousParameter['data'])) + 1;
            }
        }

        $this->setContinuousParameter($continuousParameter);
    }

    /**
     * 連続予約の制限値を取得
     *
     * @return int
     */
    protected function getContinuousLimit()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $limit = Configure::readOrFail('Setting.reservation.continuousLimit.noPayment');
        if (!$this->isAdmin() && $systemSettingsTable->getData()->usePayment()) {
            $limit = Configure::readOrFail('Setting.reservation.continuousLimit.payment');
        }

        return $limit;
    }

    /**
     * 予約フォームを生成
     *
     * @return \App\Form\Common\Reservations\ReservationForm
     */
    abstract protected function createReservationForm();
}
