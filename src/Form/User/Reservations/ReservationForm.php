<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

use App\Form\Common\Reservations\ReservationForm as CommonReservationForm;
use App\Form\User\User\TermsTrait as UserTermsTrait;
use App\Model\Entity\FormGroup;
use App\Model\Entity\ReservationStatus;
use App\Model\Table\ReservationsTable;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 予約フォーム
 */
class ReservationForm extends CommonReservationForm
{
    use ContinuousTrait;
    use TermsTrait;
    use UserTermsTrait;

    /**
     * @var bool
     */
    protected $adminFlg = false;

    /**
     * @var array|null
     */
    protected $optinData = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        if ($this->requiredUserTerms()) {
            $schema = $this->buildUserTermsSchema($schema);
        }
        if ($this->requiredReservationTerms()) {
            $schema = $this->buildReservationTermsSchema($schema);
        }

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        parent::validationDefault($validator);

        if ($this->requiredUserTerms()) {
            $this->buildUserTermsValidator($validator);
        }
        if ($this->requiredReservationTerms()) {
            $this->buildReservationTermsValidator($validator);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = parent::buildFieldValueOptions();

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        $reservationTypes = [];
        if ($this->commonData()->existsUserLoginData()) {
            $reservationTypes = [
                ReservationsTable::RESERVATION_TYPE_EXISTING_USER,
            ];
        } else {
            if ($siteSettingsTable->getData()->isUseFlgOn('reservation_add_user_flg')) {
                $reservationTypes[] = ReservationsTable::RESERVATION_TYPE_NEW_USER;
            }
            if ($siteSettingsTable->getData()->isUseFlgOn('reservation_add_not_user_flg')) {
                $reservationTypes[] = ReservationsTable::RESERVATION_TYPE_NON_USER;
            }
        }

        $fieldValueOptions['reservationType'] = [];
        foreach ($reservationTypes as $reservationType) {
            $fieldValueOptions['reservationType'][$reservationType] = __(Configure::readOrFail(
                'Master.reservation.reservationTypeWord.' . $reservationType
            ));
        }

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function getReservationFormGroups(?int $formType = null)
    {
        if (!isset($this->reservationFormGroups)) {
            // $this->reservationFormGroupsにarrayが格納される
            parent::getReservationFormGroups($formType);
            // @phpstan-ignore-next-line
            if (isset($this->reservationFormGroups[FormGroup::FORM_TYPE_USER])) {
                $reservationType = $this->getReservationParameter('reservation_type');
                if (
                    isset($this->reservationEntity)
                    || ((string)$reservationType) === ((string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER)
                ) {
                    $this->reservationFormGroups[FormGroup::FORM_TYPE_USER] = $this->getReservationDisplayUserForm(
                        $this->reservationFormGroups[FormGroup::FORM_TYPE_USER]
                    );
                }
            }
        }

        return parent::getReservationFormGroups($formType);
    }

    /**
     * @inheritDoc
     */
    protected function createUserEntity($data, $options = null)
    {
        if (isset($this->optinData)) {
            $data = Hash::merge($data, $this->optinData);
        }

        parent::createUserEntity($data, $options);
    }

    /**
     * 予約画面へ表示する会員項目を取得
     *
     * @param array $formGroups フォーム項目
     * @return array
     */
    protected function getReservationDisplayUserForm($formGroups)
    {
        $result = [];
        foreach ($formGroups as $formGroup) {
            $formGroup = clone $formGroup;

            $formItems = [];
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                if ($formItem->isReservationDisplayItem()) {
                    $formItems[] = $formItem;
                }
            }

            if (!empty($formItems)) {
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();

                $result[] = $formGroup;
            }
        }

        return $result;
    }

    /**
     * オプトインデータを設定
     *
     * @param array $optinData オプトインデータ
     * @return void
     */
    public function setOptinData(array $optinData)
    {
        $this->optinData = $optinData;
    }

    /**
     * 利用規約(会員)の表示を判定
     *
     * @return bool
     */
    public function requiredUserTerms()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (
            !$siteSettingsTable->getData()->isUseFlgOn('user_terms_flg')
            || (isset($this->userEntity) && !$this->userEntity->isNew())
        ) {
            return false;
        }

        if ($this->isConfirm()) {
            return false;
        }

        return true;
    }

    /**
     * 利用規約(予約)の表示を判定
     *
     * @return bool
     */
    public function requiredReservationTerms()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (
            !$siteSettingsTable->getData()->isUseFlgOn('reservation_terms_flg')
            || (isset($this->reservationEntity) && !$this->reservationEntity->isNew())
        ) {
            return false;
        }

        if ($this->isConfirm()) {
            return false;
        }

        return true;
    }

    /**
     * ゲスト予約がキャンセルされている場合は追加情報の出力をしない
     *
     * @return void
     */
    public function guestReservationIsCancel()
    {
        if (
            $this->getReservationEntity()->getStatus() === ReservationStatus::STATUS_TYPE_CANCEL
            || $this->getReservationEntity()->getStatus() === ReservationStatus::STATUS_TYPE_ABSENCE
        ) {
            $this->userEntity = null;
            $this->excludeUserData = true;
        }
    }

    /**
     * 会員の予約エラーをチェック
     *
     * @return string|null
     */
    public function checkUserError()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        return $reservationsTable->checkUserError($this->getReservationEntity());
    }
}
