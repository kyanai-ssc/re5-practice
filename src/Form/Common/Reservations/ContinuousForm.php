<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Form\ConfirmTransitionTrait;
use App\Locale\Message;
use App\Model\Entity\FormGroup;
use App\Model\Entity\SiteSetting;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * 連続予約フォーム
 */
abstract class ContinuousForm extends AppForm
{
    use CommonFormTrait;
    use ConfirmTransitionTrait;
    use ContinuousTrait;

    /**
     * @var int|null
     */
    protected $totalCharge;

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data);
        if (!$this->validateReservations()) {
            $result = false;
        }

        return $result;
    }

    /**
     * 連続予約の可否を判定
     *
     * @return bool 判定結果
     */
    public function canContinuous()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettings */
        $siteSettings = $this->getTableLocator()->get('SiteSettings');
        $siteSetting = $siteSettings->getData();

        if ($siteSetting->get('reservation_continuous_flg') !== SiteSetting::COMMON_USE_FLG_ON) {
            return false;
        }

        if ($this->getUserEntity()->isNew()) {
            return false;
        }

        if (count($this->getReservationEntities()) >= $this->getContinuousLimit()) {
            return false;
        }

        return true;
    }

    /**
     * 会員のフォームグループを取得
     *
     * @return array
     */
    public function getUserFormGroups()
    {
        $userFormGroups = [];
        foreach ((array)$this->getReservationForm() as $reservationForm) {
            $userFormGroups = $reservationForm->getReservationFormGroups(FormGroup::FORM_TYPE_USER);
            break;
        }

        return $userFormGroups;
    }

    /**
     * 会員のエンティティを取得
     *
     * @return \Cake\Datasource\EntityInterface
     */
    public function getUserEntity()
    {
        $userEntity = null;
        foreach ((array)$this->getReservationForm() as $reservationForm) {
            $userEntity = $reservationForm->getReservationEntity()->getUserEntity();
            break;
        }

        return $userEntity;
    }

    /**
     * 予約IDを取得
     *
     * @return array 予約ID
     */
    public function getReservationIds()
    {
        $ids = [];
        foreach ($this->getReservationEntities() as $reservation) {
            if (!$reservation->hasErrors()) {
                $ids[] = $reservation->get('id');
            }
        }

        return $ids;
    }

    /**
     * 先頭の予約IDを取得
     *
     * @return int
     */
    public function getFirstReservationId()
    {
        $ids = $this->getReservationIds();
        $id = reset($ids);
        if ($id === false) {
            throw new CakeException();
        }

        return (int)$id;
    }

    /**
     * 先頭の予約を取得
     *
     * @return \App\Model\Entity\Reservation
     */
    public function getFirstReservation()
    {
        $reservations = $this->getReservationEntities();
        $reservation = reset($reservations);
        if ($reservation === false) {
            throw new CakeException();
        }

        return $reservation;
    }

    /**
     * アプリケーションルールのエラーを取得
     *
     * @param bool $includeStockError 在庫エラー
     * @return string|null
     */
    public function getRulesError($includeStockError = false)
    {
        foreach ($this->getReservationEntities() as $reservation) {
            $user = $reservation->getUserEntity();
            if (isset($user)) {
                $userErrors = $user->getErrors();
                if (isset($userErrors['user_error'])) {
                    return reset($userErrors['user_error']);
                }
            }
            $reservationErrors = $reservation->getErrors();
            if (isset($reservationErrors['event_error'])) {
                return reset($reservationErrors['event_error']);
            }
            if ($includeStockError) {
                foreach ((array)$reservation->get('reservation_options') as $reservationOption) {
                    if (isset($reservationErrors['option_errors_' . $reservationOption->get('option_id')])) {
                        return reset($reservationErrors['option_errors_' . $reservationOption->get('option_id')]);
                    }
                }
            }
            if (isset($reservationErrors['payment_error'])) {
                return reset($reservationErrors['payment_error']);
            }
        }

        return null;
    }

    /**
     * エラーが存在するデータを取得
     *
     * @return array
     */
    public function getErrorData()
    {
        $continuousData = (array)$this->getContinuousParameter('data');
        foreach ($this->getReservationEntities() as $index => $entity) {
            if (!$entity->hasErrors()) {
                unset($continuousData[$index]);
            }
        }

        return array_values($continuousData);
    }

    /**
     * 合計料金を取得
     *
     * @return int
     */
    public function getTotalCharge()
    {
        if (!isset($this->totalCharge)) {
            $charge = 0;
            foreach ($this->getReservationEntities() as $reservation) {
                $charge += $reservation->get('charge');
            }

            $this->totalCharge = (int)$charge;
        }

        return $this->totalCharge;
    }

    /**
     * 合計の料金内訳表示を判定
     *
     * @param bool $isContinuous 続けて予約の判定
     * @param bool $hasTotalCharge 合計料金の有無
     * @param bool $requirePayments 決済の要否
     * @return bool
     */
    public function requiredChargeBreakdownAll(bool $isContinuous, bool $hasTotalCharge, bool $requirePayments)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        return $siteSettingsTable->getData()->isUseFlgOn('charge_breakdown_flg')
            && ($isContinuous && $hasTotalCharge || $requirePayments);
    }

    /**
     * 料金の有無を判定
     *
     * @return bool
     */
    public function hasCharge()
    {
        return $this->getTotalCharge() > 0;
    }

    /**
     * 予約のバリデーション
     *
     * @return bool
     */
    protected function validateReservations()
    {
        $result = true;
        $continuousData = $this->getContinuousParameter('data');
        foreach ((array)$this->getReservationForm() as $key => $reservationForm) {
            if (!$reservationForm->execute(Hash::get($continuousData, $key . '.data', []))) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 決済トークンを取得
     *
     * @return array|null
     */
    public function getPaymentTokens(): ?array
    {
        return null;
    }

    /**
     * 本人確認情報に関するエラーメッセージ調整
     *
     * @return void
     */
    public function formatKycErrorMessage()
    {
        $errors = $this->getErrors();
        // エラーメッセージ調整
        if (
            isset($errors['payment_email_address']['_empty'])
            || isset($errors['payment_phone_number']['_empty'])
        ) {
            unset($errors['payment_email_address']);
            unset($errors['payment_phone_number']);

            $errors['kyc'] = __(
                Message::ERROR_PAYMENT_KYC,
                __('payment/emailAddress'),
                __('payment/phoneNumber')
            );

            $this->setErrors($errors);
        }
    }
}
