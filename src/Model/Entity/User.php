<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\Entity\Traits\AdditionValuesTrait;
use App\Model\InputType\Item\Type\LoginNameInterface;
use App\Utility\DateTimeUtility;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\Utility\Security;

/**
 * User Entity
 *
 * @property int $id
 * @property int $user_authority_id
 * @property string|null $login_id
 * @property string|null $mail
 * @property int $guest_flg
 * @property int $withdrawal_flg
 * @property \Cake\I18n\FrozenTime|null $password_modify_timestamp
 * @property string|null $password
 * @property \Cake\I18n\FrozenDate|null $expiration_date_from
 * @property \Cake\I18n\FrozenDate|null $expiration_date_to
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\UserAuthority $user_authority
 * @property \App\Model\Entity\UserLoginHistory $user_login_history
 * @property \App\Model\Entity\AutoReplyMailHistory[] $auto_reply_mail_histories
 * @property \App\Model\Entity\BounceMailHistory[] $bounce_mail_histories
 * @property \App\Model\Entity\Inquiry[] $inquiries
 * @property \App\Model\Entity\MailDeliveryHistory[] $mail_delivery_histories
 * @property \App\Model\Entity\Reservation[] $reservations
 * @property \App\Model\Entity\UserAddition[] $user_additions
 * @property \App\Model\Entity\UserPasswordReminderToken[] $user_password_reminder_tokens
 * @property \App\Model\Entity\WaitingCancellation[] $waiting_cancellations
 * @property \App\Model\Entity\UserSmartLock $user_smart_lock
 */
class User extends AppEntity
{
    use AdditionValuesTrait;

    public const GUEST_FLG_OFF = 0;
    public const GUEST_FLG_ON = 1;

    public const WITHDRAWAL_FLG_OFF = 0;
    public const WITHDRAWAL_FLG_ON = 1;

    public const PASSWORD_CRYPT_SALT_LENGTH = 32;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'user_authority_id' => true,
        'login_id' => true,
        'mail' => true,
        'guest_flg' => false,
        'withdrawal_flg' => false,
        'password_modify_timestamp' => false,
        'password' => true,
        'expiration_date_from' => true,
        'expiration_date_to' => true,
        'created' => false,
        'modified' => false,
        'user_authority' => false,
        'user_login_history' => false,
        'auto_reply_mail_histories' => false,
        'bounce_mail_histories' => false,
        'inquiries' => false,
        'mail_delivery_histories' => false,
        'reservations' => false,
        'user_additions' => true,
        'user_password_reminder_tokens' => false,
        'waiting_cancellations' => false,
        'mail_confirm' => true,
        'addition_values' => true,
        'user_smart_lock' => true,
    ];

    protected $_virtual = [
        'mail_confirm',
        'addition_values',
        'crypt_password',
        'plain_password',
        'guest_reservation_id',
    ];
    protected $_hidden = [
        'crypt_password',
        'plain_password',
    ];

    /**
     * @var \App\Model\Entity\UserAuthority|null
     */
    protected $userAuthorityEntity = null;

    /**
     * 会員権限を取得
     *
     * @return \App\Model\Entity\UserAuthority|null 会員権限
     */
    public function getUserAuthorityEntity()
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        if (!isset($this->userAuthorityEntity) && $this->has('user_authority_id')) {
            try {
                /** @throws \Cake\Datasource\Exception\RecordNotFoundException */
                $this->userAuthorityEntity = $userAuthoritiesTable->get($this->get('user_authority_id'));
            } catch (RecordNotFoundException $e) {
                return null;
            }
        }

        return $this->userAuthorityEntity;
    }

    /**
     * 会員権限を設定
     *
     * @param \App\Model\Entity\UserAuthority $userAurhority 会員権限
     * @return void
     */
    public function setUserAuthorityEntity(UserAuthority $userAurhority)
    {
        $this->userAuthorityEntity = $userAurhority;
    }

    /**
     * 非会員を判定
     *
     * @return bool 判定結果
     */
    public function isGuest()
    {
        if ((string)$this->get('guest_flg') === ((string)static::GUEST_FLG_ON)) {
            return true;
        }

        return false;
    }

    /**
     * 退会を判定
     *
     * @return bool 判定結果
     */
    public function withdrew()
    {
        if ((string)$this->get('withdrawal_flg') === ((string)static::WITHDRAWAL_FLG_ON)) {
            return true;
        }

        return false;
    }

    /**
     * 編集可能判定
     *
     * @param bool|null $isAdmin 管理側フラグ
     * @return bool
     */
    public function canEdit($isAdmin = null)
    {
        if (!isset($isAdmin)) {
            $isAdmin = $this->commonData()->existsAdminLoginData();
        }

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if ($this->withdrew()) {
            return false;
        }
        if (!$isAdmin) {
            if ($siteSettingsTable->getData()->get('user_edit_flg') !== SiteSetting::COMMON_USE_FLG_ON) {
                return false;
            }
        }

        return true;
    }

    /**
     * 削除可能判定
     *
     * @return bool
     */
    public function canDelete()
    {
        /** @var \App\Model\Table\ReservationsTable $reservationTable */
        $reservationTable = $this->getTableLocator()->get('Reservations');
        $reserve = $reservationTable->find('count', [
            'inputs' => [
                'user_id' => $this->get('id'),
                'usage_timestamp_future' => true,
                'not_cancel' => true,
            ],
        ]);

        if ($reserve->count() >= 1) {
            return false;
        }

        return true;
    }

    /**
     * 退会可能判定
     *
     * @return bool
     */
    public function canWithdraw()
    {
        if ($this->withdrew()) {
            return false;
        }

        /** @var \App\Model\Table\ReservationsTable $reservationTable */
        $reservationTable = $this->getTableLocator()->get('Reservations');
        $reserve = $reservationTable->find('count', [
            'inputs' => [
                'user_id' => $this->get('id'),
                'usage_timestamp_future' => true,
                'not_cancel' => true,
            ],
        ]);

        if ($reserve->count() >= 1) {
            return false;
        }

        return true;
    }

    /**
     * ログイン名を取得
     *
     * @return string|null
     */
    public function getLoginName()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $formItemId = $this->get('user_authority')->get('login_name_form_item_id');
        if (((string)$formItemId) === '') {
            return null;
        }

        $formItem = $formItemsTable->getFormItem($formItemId);
        if (!isset($formItem)) {
            return null;
        }

        $inputTypeItem = $formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof LoginNameInterface)) {
            return null;
        }
        $loginName = $inputTypeItem->getLoginNameValue($this);

        return $loginName;
    }

    /**
     * パスワードをハッシュ化
     *
     * @param string $password パスワード
     * @return string|null ハッシュ化文字列
     */
    public function passwordHash(string $password)
    {
        $passwordHasher = new DefaultPasswordHasher();
        $hash = $passwordHasher->hash($password);
        if (!is_string($hash)) {
            return null;
        }

        return $hash;
    }

    /**
     * パスワードを暗号化
     *
     * @param string $password パスワード
     * @return string|null 暗号化文字列
     */
    public function encryptPassword(string $password)
    {
        $salt = Security::randomString(static::PASSWORD_CRYPT_SALT_LENGTH);
        $crypt = $salt . Security::encrypt($password, $this->generateCryptKey($salt));

        return $crypt;
    }

    /**
     * パスワードを復号化
     *
     * @param string $crypt 暗号化文字列
     * @return string|null パスワード
     */
    public function decryptPassword(string $crypt)
    {
        $salt = substr($crypt, 0, static::PASSWORD_CRYPT_SALT_LENGTH);
        $crypt = substr($crypt, static::PASSWORD_CRYPT_SALT_LENGTH);
        $password = Security::decrypt($crypt, $this->generateCryptKey($salt));
        if (!is_string($password)) {
            return null;
        }

        return $password;
    }

    /**
     * 暗号化キーを生成
     *
     * @param string $salt ソルト
     * @return string 暗号化キー
     */
    protected function generateCryptKey($salt)
    {
        return substr(Security::hash((string)$this->get('login_id'), 'sha1', $salt), 0, 32);
    }

    /**
     * パスワードのミューテーター
     *
     * @param string|null $password パスワード
     * @return string|null ハッシュ化文字列
     */
    protected function _setPassword($password)
    {
        if (((string)$password) === '') {
            return null;
        }

        if (is_null($password)) {
            return null;
        }

        return $this->passwordHash($password);
    }

    /**
     * 追加情報取得
     *
     * @param array|string|null $data データ
     * @return array
     */
    protected function _getAdditionValues($data)
    {
        if (is_array($data)) {
            return $data;
        }
        $this->_fields['addition_values'] = $this->createAdditionValues('UserAdditions', $data);

        return $this->_fields['addition_values'];
    }

    /**
     * 追加情報設定
     *
     * @param array|null $data データ
     * @return array|null
     */
    protected function _setAdditionValues($data)
    {
        // 入力値にキーとして存在しないデータをマージ
        $data = (array)$data + (array)$this->createAdditionValues('UserAdditions');

        $this->set('user_additions', $this->createAdditionEntity('UserAdditions', $data));

        return $data;
    }

    /**
     * パスワード入力値のアクセサ
     *
     * @param string|null $password パスワード
     * @return string|null パスワード
     */
    protected function _getPlainPassword($password)
    {
        if ((string)$this->get('crypt_password') === '') {
            return null;
        }

        return $this->decryptPassword($this->get('crypt_password'));
    }

    /**
     * パスワード入力値のミューテータ
     *
     * @param string|null $password パスワード
     * @return null
     */
    protected function _setPlainPassword($password)
    {
        if (((string)$password) === '') {
            return null;
        }

        if (is_null($password)) {
            return null;
        }

        $this->set('crypt_password', $this->encryptPassword($password));

        return null;
    }

    /**
     * パスワードをチェック
     *
     * @param string $password パスワード
     * @return bool
     */
    public function passwordCheck(string $password)
    {
        $passwordHasher = new DefaultPasswordHasher();

        return $passwordHasher->check($password, $this->get('password'));
    }

    /**
     * 日付が有効期間内かをチェック
     *
     * @param string|\DateTimeInterface|null $dateFrom 判定対象日付(開始)
     * @param string|\DateTimeInterface|null $dateTo 判定対象日付(終了)
     * @return bool
     */
    public function withinValidPeriod($dateFrom = null, $dateTo = null)
    {
        if (!isset($dateFrom)) {
            $dateFrom = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
        }
        if (!isset($dateTo)) {
            $dateTo = $dateFrom;
        }
        $expirationDateFrom = $this->get('expiration_date_from');
        $expirationDateTo = $this->get('expiration_date_to');

        return DateTimeUtility::isWithinDate($dateFrom, $dateTo, $expirationDateFrom, $expirationDateTo);
    }

    /**
     * 属性がセットされているかチェック
     *
     * @return bool
     */
    public function hasAttribute()
    {
        $additionValues = $this->get('addition_values');
        if (isset($additionValues[Configure::read('Setting.formItemAdditionValues.attribute')])) {
            return true;
        }

        return false;
    }
}
