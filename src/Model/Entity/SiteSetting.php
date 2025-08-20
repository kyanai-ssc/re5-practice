<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;

/**
 * SiteSetting Entity
 *
 * @property int $id
 * @property int $login_flg
 * @property int $login_required_flg
 * @property int $user_add_flg
 * @property int $user_edit_flg
 * @property int $mail_edit_optin_flg
 * @property int $optin_flg
 * @property int $id_reminder_flg
 * @property int $password_reminder_flg
 * @property int $reservation_add_user_flg
 * @property int $reservation_add_not_user_flg
 * @property int $reservation_continuous_flg
 * @property int $reservation_reminder_flg
 * @property int|null $reservation_reminder_type
 * @property int|null $reservation_reminder_time
 * @property int $inquiry_flg
 * @property int $front_public_flg
 * @property string|null $site_theme
 * @property string|null $key_visual_url
 * @property string|null $top_information
 * @property string|null $header_logo_pc
 * @property string|null $header_logo_sp
 * @property int $login_display_add_user_flg
 * @property int $top_news_number
 * @property int $top_search_label_flg
 * @property int $top_search_tag_flg
 * @property int $tag_search_method
 * @property int $top_search_event_name_flg
 * @property int $calendar_search_label_flg
 * @property int $calendar_search_tag_flg
 * @property int $calendar_search_event_name_flg
 * @property int $calendar_date_default
 * @property \Cake\I18n\FrozenTime $calendar_time_from
 * @property \Cake\I18n\FrozenTime $calendar_time_to
 * @property \Cake\I18n\FrozenTime|null $calendar_time_default
 * @property int $calendar_month_display_limit
 * @property int $reservation_form_type_first
 * @property int $reservation_edit_event_flg
 * @property int $terms_flg
 * @property int $user_terms_flg
 * @property int $reservation_terms_flg
 * @property int $sctl_flg
 * @property int $reservation_sctl_flg
 * @property int $charge_breakdown_flg
 * @property int $news_new_period_type
 * @property int $news_new_period_number
 * @property string|null $meta_keyword
 * @property string|null $meta_description
 * @property int $admin_calendar_type_default
 * @property int $reservation_edit_not_user_flg
 * @property int $reservation_close_reminder_flg
 * @property int|null $reservation_close_reminder_type
 * @property int|null $reservation_close_reminder_time
 * @property int $reservation_edit_payment_flg
 * @property int $calendar_registration_deadline_display_flg
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class SiteSetting extends AppEntity
{
    /**
     * 共通フラグ：利用する
     */
    public const COMMON_USE_FLG_ON = 1;

    /**
     * 共通フラグ：利用しない
     */
    public const COMMON_USE_FLG_OFF = 0;

    /**
     * タグ検索：OR
     */
    public const TAG_SEARCH_METHOD_OR = 1;

    /**
     * タグ検索：AND
     */
    public const TAG_SEARCH_METHOD_AND = 2;

    /**
     * ログイン利用：オフ
     */
    public const LOGIN_USE_FLG_OFF = 0;

    /**
     * ログイン利用：オン
     */
    public const LOGIN_USE_FLG_ON = 1;

    /**
     * ログイン利用：必須
     */
    public const LOGIN_USE_FLG_REQUIRE = 3;

    /**
     * リマインダーメールタイプ：時間前
     */
    public const RESERVATION_REMINDER_TYPE_TIME = 1;

    /**
     * リマインダーメールタイプ：日前
     */
    public const RESERVATION_REMINDER_TYPE_DAY = 2;

    /**
     * フォーム表示順：会員フォーム
     */
    public const RESERVATION_FORM_TYPE_FIRST_USER = 1;

    /**
     * フォーム表示順：予約フォーム
     */
    public const RESERVATION_FORM_TYPE_FIRST_RESERVE = 2;

    /**
     * カレンダーデフォルト表示日数：1週間（7日）
     */
    public const CALENDAR_TIME_DEFAULT_1WEEK = 7;

    /**
     * カレンダーデフォルト表示日数：1日
     */
    public const CALENDAR_TIME_DEFAULT_1DAY = 1;

    /**
     * 公開側表示フラグ：オフ
     */
    public const FRONT_PUBLIC_FLG_OFF = 0;

    /**
     * 公開側表示フラグ：オン
     */
    public const FRONT_PUBLIC_FLG_ON = 1;

    /**
     * お知らせNew表示期間タイプ：時間
     */
    public const NEWS_NEW_PERIOD_TYPE_TIME = 1;

    /**
     * お知らせNew表示期間タイプ：日間
     */
    public const NEWS_NEW_PERIOD_TYPE_DAY = 2;

    /**
     * 予約受付締切を過ぎたコマ：オフ
     */
    public const CALENDAR_REGISTRATION_DEADLINE_DISPLAY_FLG_OFF = 0;

    /**
     * 予約受付締切を過ぎたコマ：オン
     */
    public const CALENDAR_REGISTRATION_DEADLINE_DISPLAY_FLG_ON = 1;

    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'login_flg' => false,
        'login_required_flg' => false,
        'user_add_flg' => false,
        'user_edit_flg' => false,
        'mail_edit_optin_flg' => false,
        'optin_flg' => false,
        'id_reminder_flg' => false,
        'password_reminder_flg' => false,
        'reservation_add_user_flg' => false,
        'reservation_add_not_user_flg' => false,
        'reservation_continuous_flg' => false,
        'reservation_reminder_flg' => false,
        'reservation_reminder_type' => false,
        'reservation_reminder_time' => false,
        'inquiry_flg' => false,
        'front_public_flg' => false,
        'site_theme' => false,
        'key_visual_url' => false,
        'top_information' => false,
        'header_logo_pc' => false,
        'header_logo_sp' => false,
        'login_display_add_user_flg' => false,
        'top_news_number' => false,
        'top_search_label_flg' => false,
        'top_search_tag_flg' => false,
        'tag_search_method' => false,
        'top_search_event_name_flg' => false,
        'calendar_search_label_flg' => false,
        'calendar_search_tag_flg' => false,
        'calendar_search_event_name_flg' => false,
        'calendar_date_default' => false,
        'calendar_time_from' => false,
        'calendar_time_to' => false,
        'calendar_time_default' => false,
        'calendar_month_display_limit' => false,
        'reservation_form_type_first' => false,
        'reservation_edit_event_flg' => false,
        'terms_flg' => false,
        'user_terms_flg' => false,
        'reservation_terms_flg' => false,
        'sctl_flg' => false,
        'reservation_sctl_flg' => false,
        'charge_breakdown_flg' => false,
        'news_new_period_type' => false,
        'news_new_period_number' => false,
        'meta_keyword' => false,
        'meta_description' => false,
        'admin_calendar_type_default' => false,
        'reservation_edit_not_user_flg' => false,
        'reservation_close_reminder_flg' => false,
        'reservation_close_reminder_type' => false,
        'reservation_close_reminder_time' => false,
        'reservation_edit_payment_flg' => false,
        'calendar_registration_deadline_display_flg' => false,
        'created' => false,
        'modified' => false,
        'login_use_flg' => false,
        'reminder_mail_hour' => false,
        'reminder_mail_day' => false,
        'reservation_close_reminder_mail_hour' => false,
        'reservation_close_reminder_mail_day' => false,
        'news_new_period_number_hour' => false,
        'news_new_period_number_day' => false,
    ];

    protected $_virtual = [
        'login_use_flg',
        'reminder_mail_hour',
        'reminder_mail_day',
        'reservation_close_reminder_mail_hour',
        'reservation_close_reminder_mail_day',
        'news_new_period_number_hour',
        'news_new_period_number_day',
    ];

    /**
     * @var \Cake\I18n\FrozenDate|null
     */
    protected $calendarDefaultDate = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $calendarTimeFrom = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $calendarTimeTo = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $calendarTimeFromHour = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $calendarTimeToHour = null;

    /**
     * @var \Cake\I18n\FrozenTime|null
     */
    protected $calendarTimeDefault = null;

    /**
     * login_use_flgのミューテータ
     *
     * @param mixed $loginFlg 値
     * @return void
     */
    protected function _setLoginUseFlg($loginFlg)
    {
        if ((string)$loginFlg === (string)static::LOGIN_USE_FLG_REQUIRE) {
            $this->set('login_flg', static::LOGIN_USE_FLG_ON);
            $this->set('login_required_flg', static::COMMON_USE_FLG_ON);
        } elseif ((string)$loginFlg === (string)static::LOGIN_USE_FLG_ON) {
            $this->set('login_flg', static::LOGIN_USE_FLG_ON);
            $this->set('login_required_flg', static::COMMON_USE_FLG_OFF);
        } else {
            $this->set('login_flg', static::LOGIN_USE_FLG_OFF);
            $this->set('login_required_flg', static::COMMON_USE_FLG_OFF);
        }
    }

    /**
     * login_use_flgのアクセサ
     *
     * @param mixed $loginFlg 値
     * @return int|null
     */
    protected function _getLoginUseFlg($loginFlg)
    {
        if (is_null($this->get('login_flg'))) {
            return null;
        }

        if ($loginFlg === null) {
            $loginFlg = $this->get('login_flg');
        }

        if ($this->get('login_required_flg') === static::COMMON_USE_FLG_ON) {
            $loginFlg = static::LOGIN_USE_FLG_REQUIRE;
        }

        return $loginFlg;
    }

    /**
     * 時間を24時表記に変更
     *
     * @param mixed $time timeオブジェクト
     * @return string 00:00～24:00の文字列
     */
    protected function _getCalendarTimeTo($time)
    {
        return $this->formatTime24($time, true);
    }

    /**
     * calendar_time_fromのアクセサ
     *
     * @param mixed $time 値
     * @return string
     */
    protected function _getCalendarTimeFrom($time)
    {
        return $this->formatTime24($time);
    }

    /**
     * calendar_time_defaultのアクセサ
     *
     * @param mixed $time 値
     * @return string
     */
    protected function _getCalendarTimeDefault($time)
    {
        return $this->formatTime24($time);
    }

    /**
     * 仮想プロパティ：リマインダー日前
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getReminderMailDay($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('reservation_reminder_time'))) {
            return $this->get('reservation_reminder_time');
        }

        return null;
    }

    /**
     * 仮想プロパティ：リマインダー時間前
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getReminderMailHour($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('reservation_reminder_time'))) {
            return $this->get('reservation_reminder_time');
        }

        return null;
    }

    /**
     * 仮想プロパティ：利用終了リマインダー日前
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getReservationCloseReminderMailDay($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('reservation_close_reminder_time'))) {
            return $this->get('reservation_close_reminder_time');
        }

        return null;
    }

    /**
     * 仮想プロパティ：利用終了リマインダー時間前
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getReservationCloseReminderMailHour($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('reservation_close_reminder_time'))) {
            return $this->get('reservation_close_reminder_time');
        }

        return null;
    }

    /**
     * 仮想プロパティ：お知らせNew表示（日間）
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getNewsNewPeriodNumberDay($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('news_new_period_number'))) {
            return $this->get('news_new_period_number');
        }

        return null;
    }

    /**
     * 仮想プロパティ：お知らせNew表示（時間）
     *
     * @param string|null $val 値
     * @return mixed
     */
    protected function _getNewsNewPeriodNumberHour($val)
    {
        if ($val !== null) {
            return $val;
        }

        if (!is_null($this->get('news_new_period_number'))) {
            return $this->get('news_new_period_number');
        }

        return null;
    }

    /**
     * CMS設定で編集可能な情報のみdirty設定
     *
     * @return void
     */
    public function setCmsAccess()
    {
        $this->setAccess('site_theme', true);
        $this->setAccess('header_logo_pc', true);
        $this->setAccess('header_logo_sp', true);
        $this->setAccess('meta_keyword', true);
        $this->setAccess('meta_description', true);
        $this->setAccess('key_visual_url', true);
        $this->setAccess('top_information', true);
    }

    /**
     * サイト基本設定で編集可能な情報のみdirty設定
     *
     * @return void
     */
    public function setSystemAccess()
    {
        $this->setAccess('login_use_flg', true);
        $this->setAccess('login_required_flg', true);
        $this->setAccess('reminder_mail_hour', true);
        $this->setAccess('reminder_mail_day', true);
        $this->setAccess('password_reminder_flg', true);
        $this->setAccess('login_display_add_user_flg', true);
        $this->setAccess('user_add_flg', true);
        $this->setAccess('user_edit_flg', true);
        $this->setAccess('mail_edit_optin_flg', true);
        $this->setAccess('optin_flg', true);
        $this->setAccess('id_reminder_flg', true);
        $this->setAccess('reception_period_time', true);
        $this->setAccess('reservation_add_user_flg', true);
        $this->setAccess('reservation_add_not_user_flg', true);
        $this->setAccess('reservation_continuous_flg', true);
        $this->setAccess('reservation_reminder_flg', true);
        $this->setAccess('reservation_reminder_type', true);
        $this->setAccess('reservation_reminder_time', true);
        $this->setAccess('inquiry_flg', true);
        $this->setAccess('front_public_flg', true);
        $this->setAccess('top_information', true);
        $this->setAccess('tag_search_method', true);
        $this->setAccess('top_search_label_flg', true);
        $this->setAccess('top_search_tag_flg', true);
        $this->setAccess('calendar_search_label_flg', true);
        $this->setAccess('top_search_event_name_flg', true);
        $this->setAccess('calendar_search_tag_flg', true);
        $this->setAccess('calendar_search_event_name_flg', true);
        $this->setAccess('calendar_date_default', true);
        $this->setAccess('calendar_time_from', true);
        $this->setAccess('calendar_time_to', true);
        $this->setAccess('calendar_time_default', true);
        $this->setAccess('calendar_month_display_limit', true);
        $this->setAccess('reservation_form_type_first', true);
        $this->setAccess('reservation_edit_event_flg', true);
        $this->setAccess('terms_flg', true);
        $this->setAccess('user_terms_flg', true);
        $this->setAccess('reservation_terms_flg', true);
        $this->setAccess('sctl_flg', true);
        $this->setAccess('reservation_sctl_flg', true);
        $this->setAccess('charge_breakdown_flg', true);
        $this->setAccess('admin_calendar_type_default', true);
        $this->setAccess('reservation_edit_not_user_flg', true);
        $this->setAccess('reservation_close_reminder_flg', true);
        $this->setAccess('reservation_close_reminder_type', true);
        $this->setAccess('reservation_close_reminder_mail_hour', true);
        $this->setAccess('reservation_close_reminder_mail_day', true);
        $this->setAccess('reservation_close_reminder_time', true);
        $this->setAccess('reservation_edit_payment_flg', true);
        $this->setAccess('calendar_registration_deadline_display_flg', true);
    }

    /**
     * お知らせ設定で編集可能な情報のみdirty設定
     *
     * @return void
     */
    public function setNewsSettingAccess()
    {
        $this->setAccess('news_new_period_number', true);
        $this->setAccess('news_new_period_type', true);
        $this->setAccess('top_news_number', true);
        $this->setAccess('news_new_period_number_hour', true);
        $this->setAccess('news_new_period_number_day', true);
    }

    /**
     * 編集可否
     *
     * @param string $field 項目名
     * @return bool
     */
    public function canEdit($field): bool
    {
        $canEdit = Configure::readOrFail('Master.system.canEdit');

        return Hash::get($canEdit, $field, true);
    }

    /**
     * 共通の利用フラグをチェック
     *
     * @param string $field 項目名
     * @return bool
     */
    public function isUseFlgOn($field)
    {
        if ((string)$this->get($field) !== ((string)static::COMMON_USE_FLG_ON)) {
            return false;
        }

        return true;
    }

    /**
     * カレンダーのデフォルト表示日を取得
     *
     * @return \Cake\I18n\FrozenDate
     */
    public function getCalendarDefaultDate()
    {
        if (!isset($this->calendarDefaultDate)) {
            $calendarDefaultDate = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
            $calendarDefaultDate = $calendarDefaultDate->addDays($this->get('calendar_date_default'));

            $this->calendarDefaultDate = $calendarDefaultDate;
        }

        return $this->calendarDefaultDate;
    }

    /**
     * カレンダーの開始時間を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getCalendarTimeFrom()
    {
        if (!isset($this->calendarTimeFrom)) {
            $calendarTimeFrom = DateTimeUtility::convertToTimeObject($this->get('calendar_time_from'));
            if (!isset($calendarTimeFrom)) {
                throw new CakeException();
            }

            $this->calendarTimeFrom = new FrozenTime(
                $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $calendarTimeFrom->format('H:i:s')
            );
        }

        return $this->calendarTimeFrom;
    }

    /**
     * カレンダーの終了時間を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getCalendarTimeTo()
    {
        if (!isset($this->calendarTimeTo)) {
            $calendarTimeTo = DateTimeUtility::convertToTimeObject($this->get('calendar_time_to'));
            if (!isset($calendarTimeTo)) {
                throw new CakeException();
            }

            $this->calendarTimeTo = new FrozenTime(
                $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $calendarTimeTo->format('H:i:s')
            );
        }

        return $this->calendarTimeTo;
    }

    /**
     * カレンダーの開始時間を時間単位で取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getCalendarTimeFromHour()
    {
        if (!isset($this->calendarTimeFromHour)) {
            $calendarTimeFromHour = $this->getCalendarTimeFrom();
            if ($calendarTimeFromHour->format('i:s') !== '00:00') {
                $calendarTimeFromHour = $calendarTimeFromHour->setTime((int)$calendarTimeFromHour->format('G'), 0, 0);
            }

            $this->calendarTimeFromHour = $calendarTimeFromHour;
        }

        return $this->calendarTimeFromHour;
    }

    /**
     * カレンダーの終了時間を時間単位で取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getCalendarTimeToHour()
    {
        if (!isset($this->calendarTimeToHour)) {
            $calendarTimeToHour = $this->getCalendarTimeTo();
            if ($calendarTimeToHour->format('i:s') !== '00:00') {
                $calendarTimeToHour = $calendarTimeToHour->setTime((int)$calendarTimeToHour->format('G') + 1, 0, 0);
            }

            $this->calendarTimeToHour = $calendarTimeToHour;
        }

        return $this->calendarTimeToHour;
    }

    /**
     * カレンダーの時間帯に重複するか判定
     *
     * @param string|\DateTimeInterface $timeFrom 開始時間
     * @param string|\DateTimeInterface $timeTo 終了時間
     * @return bool
     */
    public function isOverlapCalendarTimeRange($timeFrom, $timeTo)
    {
        $calendarTimeFrom = $this->getCalendarTimeFrom();
        $calendarTimeTo = $this->getCalendarTimeTo();
        if (!DateTimeUtility::isOverlapTime($timeFrom, $timeTo, $calendarTimeFrom, $calendarTimeTo)) {
            return false;
        }

        return true;
    }

    /**
     * カレンダーのデフォルト表示時間を取得
     *
     * @return \Cake\I18n\FrozenTime|null
     */
    public function getCalendarTimeDefault()
    {
        if (!isset($this->calendarTimeDefault)) {
            $calendarTimeDefault = DateTimeUtility::convertToTimeObject($this->get('calendar_time_default'));
            if (isset($calendarTimeDefault)) {
                $this->calendarTimeDefault = new FrozenTime(
                    $this->commonData()->getNowDateTime()->format('Y-m-d') . ' ' . $calendarTimeDefault->format('H:i:s')
                );
            }
        }

        return $this->calendarTimeDefault;
    }

    /**
     * リマインダーの日時を取得
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $targetDateTime 対象日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getReminderDateTime(int $reminderInterval, $targetDateTime)
    {
        $reminderDateTime = DateTimeUtility::truncateMinute($targetDateTime, $reminderInterval);
        $reminderDateTime = $reminderDateTime->addMinutes($this->getReminderMinute());

        return $reminderDateTime;
    }

    /**
     * 利用終了リマインダーの日時を取得
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $targetDateTime 対象日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getCloseReminderDateTime(int $reminderInterval, $targetDateTime)
    {
        $reminderDateTime = DateTimeUtility::truncateMinute($targetDateTime, $reminderInterval);
        $reminderDateTime = $reminderDateTime->addMinutes($this->getCloseReminderMinute());

        return $reminderDateTime;
    }

    /**
     * 予約開始日時を基準にリマインダーの日時を取得
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $usageTimestampFrom 予約開始日時
     * @return \Cake\I18n\FrozenTime
     */
    public function getReminderUsageTimestamp(int $reminderInterval, $usageTimestampFrom)
    {
        $reminderUsageTimestamp = DateTimeUtility::truncateMinute($usageTimestampFrom, $reminderInterval);
        $reminderUsageTimestamp = $reminderUsageTimestamp->subMinutes($this->getReminderMinute());

        return $reminderUsageTimestamp;
    }

    /**
     * 予約終了日時を基準にリマインダーの日時を取得
     *
     * @param int $reminderInterval 送信間隔
     * @param string|\DateTimeInterface $usageTimestampTo 予約終了日時
     * @return \Cake\I18n\FrozenTime
     */
    public function getCloseReminderUsageTimestamp(int $reminderInterval, $usageTimestampTo)
    {
        $reminderUsageTimestamp = DateTimeUtility::truncateMinute($usageTimestampTo, $reminderInterval);
        $reminderUsageTimestamp = $reminderUsageTimestamp->subMinutes($this->getCloseReminderMinute());

        return $reminderUsageTimestamp;
    }

    /**
     * リマインダーの設定を分単位で取得
     *
     * @return int
     */
    public function getReminderMinute()
    {
        $minute = $this->get('reservation_reminder_time');
        if ((string)$this->get('reservation_reminder_type') === ((string)static::RESERVATION_REMINDER_TYPE_DAY)) {
            $minute *= 24 * 60;
        }

        return $minute;
    }

    /**
     * 利用終了リマインダーの設定を分単位で取得
     *
     * @return int
     */
    public function getCloseReminderMinute()
    {
        $minute = $this->get('reservation_close_reminder_time');
        if ((string)$this->get('reservation_close_reminder_type') === ((string)static::RESERVATION_REMINDER_TYPE_DAY)) {
            $minute *= 24 * 60;
        }

        return $minute;
    }

    /**
     * 会員の予約画面表示可否を判定
     *
     * @return bool
     */
    public function canAccessUserReservation()
    {
        if (!$this->commonData()->existsUserLoginData()) {
            if (
                !$this->isUseFlgOn('reservation_add_user_flg') && !$this->isUseFlgOn('reservation_add_not_user_flg')
                && !$this->isUseFlgOn('login_flg')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * 登録締切日時を過ぎたコマをカレンダーに表示するか
     *
     * @return bool
     */
    public function isCalendarRegistrationDeadlineDisplay()
    {
        return (string)$this->get('calendar_registration_deadline_display_flg') ===
            (string)static::CALENDAR_REGISTRATION_DEADLINE_DISPLAY_FLG_ON;
    }
}
