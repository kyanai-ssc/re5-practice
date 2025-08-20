<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * AdminOperationalLog Entity
 *
 * @property int $id
 * @property int $admin_id
 * @property string $login_id
 * @property int $operated_function
 * @property int $operated_type
 * @property string|null $operated_data
 * @property string|null $edited_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Admin $admin
 */
class AdminOperationalLog extends AppEntity
{
    /**
     * 操作ログ機能名
     */
    public const FUNCTION_ADMINS = 1; // 管理者管理
    public const FUNCTION_SITE_SETTING = 2; // サイト基本設定
    public const FUNCTION_LABELS = 3; // ラベル管理
    public const FUNCTION_TAGS = 4; // タグ管理
    public const FUNCTION_OPTIONS = 5; // オプション管理
    public const FUNCTION_EVENTS = 6; // 予約枠管理
    public const FUNCTION_AUTO_REPLY_MAILS = 7; // 休日設定
    public const FUNCTION_EVENT_HOLIDAYS = 8; // 休日設定
    public const FUNCTION_RESERVATION_FORM_PATTERNS = 9; // 予約フォーム項目パターン設定
    public const FUNCTION_USER_FORM_PATTERNS = 10; // 会員フォーム項目パターン設定
    public const FUNCTION_USER_AUTHORITIES = 11; // 会員権限管理
    public const FUNCTION_FILE_GROUPS = 12; // ファイル管理
    public const FUNCTION_NEWS = 13; // お知らせ管理
    public const FUNCTION_TERMS = 14; // 利用規約
    public const FUNCTION_CMS = 15; // サイトデザイン設定
    public const FUNCTION_ANALYSIS_TAGS = 16; // 計測タグ設定
    public const FUNCTION_COLOR_CHIPS = 17; // カラーチップ設定
    public const FUNCTION_WORDS = 18; // 文言管理
    public const FUNCTION_HOLIDAYS = 19; // 祝日管理
    public const FUNCTION_MAIL_DELIVERIES = 20; //メール配信機能
    public const FUNCTION_BOUNCE_MAILS = 21; //不達メール
    public const FUNCTION_FORM_GROUPS_USERS = 22; //フォームグループ(会員)
    public const FUNCTION_USERS = 23; //顧客管理
    public const FUNCTION_RESERVATIONS = 24; //予約管理
    public const FUNCTION_FORM_GROUPS_RESERVATIONS = 25; //フォームグループ(予約)
    public const FUNCTION_ORGANIZERS = 26; //主催者設定
    public const FUNCTION_ADMIN_AUTHORITIES = 27; //利用許可画面のパターン設定
    public const FUNCTION_RECEPTION_STATUSES = 28; //受付状況一覧
    public const FUNCTION_SMART_LOCKS = 29; //Akerun設定
    public const FUNCTION_ZOOM_CONNECT_USERS = 30; //Zoom連携ユーザー管理
    public const FUNCTION_RECAPTCHA = 31; //reCAPTCHAv3設定
    public const FUNCTION_PAYMENT_ERRORS = 32; //決済エラー回数一覧
    public const FUNCTION_PAYMENT_SETTING = 33; //決済設定

    /**
     * 操作ログ操作名
     */
    public const TYPE_ADD = 1;
    public const TYPE_EDIT = 2;
    public const TYPE_DELETE = 3;
    public const TYPE_TOGETHER_EDIT = 4;
    public const TYPE_UPDATE_PUBLIC = 5;
    public const TYPE_NEWS_SETTING = 6;
    public const TYPE_WORD_EDIT = 7;
    public const TYPE_ERROR_WORD_EDIT = 8;
    public const TYPE_STATUS_WORD_EDIT = 9;
    public const TYPE_COPY = 10;
    public const TYPE_PREF_WORD_EDIT = 11;
    public const TYPE_SEND_EXCLUDE_OFF = 12;
    public const TYPE_SEND_EXCLUDE_ON = 13;
    public const TYPE_TOGETHER_DELETE = 14;
    public const TYPE_PAYMENT_METHOD_WORD_EDIT = 15;
    public const TYPE_PAYMENT_STATUS_WORD_EDIT = 16;
    public const TYPE_WITHDRAW = 17;
    public const TYPE_CANCEL = 18;
    public const TYPE_CSV_UPLOAD = 19;
    public const TYPE_UPDATE_STATUS = 20;
    public const TYPE_RECEPTION_STATUS_WORD_EDIT = 21;
    public const TYPE_AKERUN = 22;
    public const TYPE_UNLOCK = 23;

    /**
     * 特殊操作ログ：全予約枠休日設定
     */
    public const EXCEPTION_ID_EVENT_HOLIDAYS = '全予約枠休日設定';

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'admin_id' => true,
        'login_id' => true,
        'operated_function' => true,
        'operated_type' => true,
        'operated_data' => true,
        'edited_id' => true,
        'created' => false,
        'modified' => false,
        'admin' => false,
    ];

    /**
     * ins_timestampのアクセサ
     *
     * @param mixed $val 値
     * @return string
     */
    protected function _getInsTimestamp($val)
    {
        return $val->i18nFormat('yyyy-MM-dd HH:mm:ss');
    }
}
