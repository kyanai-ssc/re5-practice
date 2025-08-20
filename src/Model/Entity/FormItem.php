<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Model\InputType\AbstractInputTypeItem;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Exception\CakeException;

/**
 * FormItem Entity
 *
 * @property int $id
 * @property int $form_group_id
 * @property int $input_type
 * @property string|null $name
 * @property int $required_flg
 * @property int $reservation_display_flg
 * @property string|null $description
 * @property int $sort_no
 * @property int $default_flg
 * @property int|null $smart_lock_type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\FormGroup $form_group
 * @property \App\Model\Entity\FormItemOptionGroup $form_item_option_group
 * @property \App\Model\Entity\EventRemark[] $event_remarks
 * @property \App\Model\Entity\FormItemChoice[] $form_item_choices
 * @property \App\Model\Entity\FormItemDetail[] $form_item_details
 * @property \App\Model\Entity\FormPatternDisplayType[] $form_pattern_display_types
 * @property \App\Model\Entity\ReservationAddition[] $reservation_additions
 * @property \App\Model\Entity\ReservationOption[] $reservation_options
 * @property \App\Model\Entity\UserAddition[] $user_additions
 * @property \App\Model\Entity\UserAuthority[] $user_authorities
 */
class FormItem extends AppEntity
{
    /**
     * 入力タイプ 会員権限
     *
     * @var int
     */
    public const INPUT_TYPE_USER_AUTHORITY = 1;

    /**
     * 入力タイプ メールアドレス
     *
     * @var int
     */
    public const INPUT_TYPE_MAIL = 2;

    /**
     * 入力タイプ メールアドレス（確認）
     *
     * @var int
     */
    public const INPUT_TYPE_MAIL_CONFIRM = 3;

    /**
     * 入力タイプ ログインID
     *
     * @var int
     */
    public const INPUT_TYPE_LOGIN_ID = 4;

    /**
     * 入力タイプ パスワード
     *
     * @var int
     */
    public const INPUT_TYPE_PASSWORD = 5;

    /**
     * 入力タイプ パスワード（確認）
     *
     * @var int
     */
    public const INPUT_TYPE_PASSWORD_CONFIRM = 6;

    /**
     * 入力タイプ 利用日
     *
     * @var int
     */
    public const INPUT_TYPE_USAGE_DATE = 7;

    /**
     * 入力タイプ 利用時間
     *
     * @var int
     */
    public const INPUT_TYPE_USAGE_TIME = 8;

    /**
     * 入力タイプ 予約時間
     *
     * @var int
     */
    public const INPUT_TYPE_RESERVATION_TIME = 9;

    /**
     * 入力タイプ 予約数
     *
     * @var int
     */
    public const INPUT_TYPE_RESERVATION_NUMBER = 10;

    /**
     * 入力タイプ 【予約枠】ラベル
     *
     * @var int
     */
    public const INPUT_TYPE_LABEL = 11;

    /**
     * 入力タイプ 【予約枠】タグ
     *
     * @var int
     */
    public const INPUT_TYPE_TAG = 12;

    /**
     * 入力タイプ 【予約枠】名称
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_NAME = 13;

    /**
     * 入力タイプ 【予約枠】予約利用期間
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_SCHEDULE_DATE = 14;

    /**
     * 入力タイプ 【予約枠】実施時間
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_SCHEDULE_TIME = 15;

    /**
     * 入力タイプ 【予約枠】受付時間
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_USAGE_TIME = 16;

    /**
     * 入力タイプ 【予約枠】インターバル時間
     *
     * @var int
     */
    public const INPUT_TYPE_INTERVAL_TIME = 17;

    /**
     * 入力タイプ 【予約枠】料金
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_CHARGE = 18;

    /**
     * 入力タイプ 【予約枠】予約受付締切タイミング
     *
     * @var int
     */
    public const INPUT_TYPE_REGISTRATION_DEADLINE = 19;

    /**
     * 入力タイプ 【予約枠】予約変更締切タイミング
     *
     * @var int
     */
    public const INPUT_TYPE_EDITING_DEADLINE = 20;

    /**
     * 入力タイプ 【予約枠】予約キャンセル締切タイミング
     *
     * @var int
     */
    public const INPUT_TYPE_CANCELLATION_DEADLINE = 21;

    /**
     * 入力タイプ テキストボックス
     *
     * @var int
     */
    public const INPUT_TYPE_TEXT = 22;

    /**
     * 入力タイプ テキストエリア
     *
     * @var int
     */
    public const INPUT_TYPE_TEXTAREA = 23;

    /**
     * 入力タイプ マルチテキストボックス
     *
     * @var int
     */
    public const INPUT_TYPE_MULTI_TEXTBOX = 24;

    /**
     * 入力タイプ ラジオボタン
     *
     * @var int
     */
    public const INPUT_TYPE_RADIO = 25;

    /**
     * 入力タイプ セレクトボックス
     *
     * @var int
     */
    public const INPUT_TYPE_SELECT = 26;

    /**
     * 入力タイプ チェックボックス
     *
     * @var int
     */
    public const INPUT_TYPE_CHECKBOX = 27;

    /**
     * 入力タイプ 年月日
     *
     * @var int
     */
    public const INPUT_TYPE_DATE_SELECT = 28;

    /**
     * 入力タイプ 氏名
     *
     * @var int
     */
    public const INPUT_TYPE_FULL_NAME = 29;

    /**
     * 入力タイプ 電話番号
     *
     * @var int
     */
    public const INPUT_TYPE_PHONE_NUMBER = 30;

    /**
     * 入力タイプ 都道府県
     *
     * @var int
     */
    public const INPUT_TYPE_PREFECTURE = 31;

    /**
     * 入力タイプ 住所検索
     *
     * @var int
     */
    public const INPUT_TYPE_SEARCH_ADDRESS = 32;

    /**
     * 入力タイプ オプション予約
     *
     * @var int
     */
    public const INPUT_TYPE_RESERVATION_OPTION = 33;

    /**
     * 入力タイプ 予約枠備考
     *
     * @var int
     */
    public const INPUT_TYPE_EVENT_REMARK = 34;

    /**
     * 入力タイプ ビデオ会議主催者
     *
     * @var int
     */
    public const INPUT_TYPE_VIDEO_MEETING_ORGANIZER = 35;

    /**
     * 入力タイプ ビデオ会議種別
     *
     * @var int
     */
    public const INPUT_TYPE_VIDEO_MEETING_TYPE = 36;

    /**
     * 入力タイプ ビデオ会議URL
     *
     * @var int
     */
    public const INPUT_TYPE_VIDEO_MEETING_URL = 37;

    /**
     * 入力タイプ ビデオ会議ID
     *
     * @var int
     */
    public const INPUT_TYPE_VIDEO_MEETING_ID = 38;

    /**
     * 入力タイプ ビデオ会議パスワード
     *
     * @var int
     */
    public const INPUT_TYPE_VIDEO_MEETING_PASSWORD = 39;

    /**
     * 入力タイプ 有効期間
     *
     * @var int
     */
    public const INPUT_TYPE_EXPIRATION_DATE = 40;

    /**
     * 入力タイプ AkerunユーザーID
     *
     * @var int
     */
    public const INPUT_TYPE_AKERUN_USER_ID = 41;

    /**
     * 必須フラグ 任意
     *
     * @var int
     */
    public const REQUIRED_FLG_OFF = 0;

    /**
     * 必須フラグ 必須
     *
     * @var int
     */
    public const REQUIRED_FLG_ON = 1;

    /**
     * 予約画面表示 非表示
     *
     * @var int
     */
    public const RESERVATION_DISPLAY_FLG_OFF = 0;

    /**
     * 予約画面表示 表示
     *
     * @var int
     */
    public const RESERVATION_DISPLAY_FLG_ON = 1;

    /**
     * 予約画面表示 OFF
     *
     * @var int
     */
    public const DEFAULT_FLG_OFF = 0;

    /**
     * 予約画面表示 ON
     *
     * @var int
     */
    public const DEFAULT_FLG_ON = 1;

    /**
     * スマートロック インスタンス
     *
     * @var \App\Utility\SmartLock\SmartLockLinkage|null|false
     */
    protected $smartLock = null;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'form_group_id' => false,
        'input_type' => true,
        'name' => true,
        'required_flg' => true,
        'reservation_display_flg' => true,
        'description' => true,
        'sort_no' => false,
        'default_flg' => false,
        'smart_lock_type' => false,
        'created' => false,
        'modified' => false,
        'form_group' => false,
        'form_item_option_group' => true,
        'event_remarks' => false,
        'form_item_choices' => true,
        'form_item_details' => true,
        'form_pattern_display_types' => false,
        'reservation_additions' => false,
        'reservation_options' => false,
        'user_additions' => false,
        'user_authorities' => false,
        'session_key' => true,
    ];

    /**
     * @inheritDoc
     */
    protected $_virtual = [
        'session_key',
    ];

    /**
     * @var \App\Model\InputType\AbstractInputTypeItem|null
     */
    protected $inputTypeItem = null;

    /**
     * 必須項目を判定
     *
     * @return bool 判定結果
     */
    public function isRequiredItem()
    {
        if ((string)$this->get('required_flg') !== ((string)static::REQUIRED_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * 予約画面表示を判定
     *
     * @return bool 判定結果
     */
    public function isReservationDisplayItem()
    {
        if ((string)$this->get('reservation_display_flg') !== ((string)static::RESERVATION_DISPLAY_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * デフォルト項目を判定
     *
     * @return bool 判定結果
     */
    public function isDefaultItem()
    {
        if ((string)$this->get('default_flg') !== ((string)static::DEFAULT_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * 削除可否判定
     *
     * @return bool 判定結果
     */
    public function canDelete()
    {
        if ($this->isDefaultItem()) {
            return false;
        }

        if ($this->smartLock === null) {
            $smartLock = new SmartLockLinkage();
            $this->setSmartLockInstance($smartLock);
        }
        if ($this->isFormItemIdForAkerun()) {
            return false;
        }

        return true;
    }

    /**
     * 入力項目を取得
     *
     * @return \App\Model\InputType\AbstractInputTypeItem 入力項目
     */
    public function getInputTypeItem()
    {
        if (!isset($this->inputTypeItem)) {
            throw new CakeException();
        }

        return $this->inputTypeItem;
    }

    /**
     * 入力項目を設定
     *
     * @param \App\Model\InputType\AbstractInputTypeItem $inputTypeItem 入力項目
     * @return void
     */
    public function setInputTypeItem(AbstractInputTypeItem $inputTypeItem)
    {
        $this->inputTypeItem = $inputTypeItem;
    }

    /**
     * clone method.
     *
     * @return void
     */
    public function __clone()
    {
        if (isset($this->inputTypeItem)) {
            $this->inputTypeItem = clone $this->inputTypeItem;
        }
    }

    /**
     * スマートロック　インスタンスを設定
     *
     * @param \App\Utility\SmartLock\SmartLockLinkage|false $instance スマートロックインスタンス
     * @return void
     */
    public function setSmartLockInstance($instance)
    {
        if ($instance === false) {
            $this->smartLock = false;
        } else {
            $this->smartLock = $instance;
        }
    }

    /**
     * Akerunユーザー名連携する会員項目を判定
     *
     * @return bool 判定結果
     */
    public function isFormItemIdForAkerun()
    {
        if ($this->smartLock === null || $this->smartLock === false) {
            return false;
        }
        if (!$this->smartLock->useSmartLock()) {
            return false;
        }

        if (is_null($this->get('id'))) {
            return false;
        }
        if ((string)$this->get('id') !== (string)$this->smartLock->getFormItemId()) {
            return false;
        }

        return true;
    }
}
