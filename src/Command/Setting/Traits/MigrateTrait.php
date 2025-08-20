<?php
declare(strict_types=1);

namespace App\Command\Setting\Traits;

/**
 * Trait MigrateTrait
 */
trait MigrateTrait
{
    /**
     * @var array
     */
    protected $masterTables = [
        'labels',
        'site_settings',
        'file_groups',
        'files',
        'terms',
        'prefectures',
        'reservation_statuses',
        'color_chips',
        'payment_methods',
        'payment_statuses',
        'options',
        'option_stock_settings',
        'form_groups',
        'form_items',
        'form_item_details',
        'form_item_choices',
        'form_item_option_groups',
        'form_item_options',
        'form_patterns',
        'form_pattern_display_types',
        'form_pattern_options',
        'user_authorities',
        'tag_groups',
        'tags',
        'auto_reply_mails',
        'auto_reply_mail_statuses',
        'holidays',
        'events',
        'event_tags',
        'event_weeks',
        'event_plans',
        'event_stock_marks',
        'event_remarks',
        'event_images',
        'event_stock_settings',
        'event_stock_setting_weeks',
        'event_stock_setting_exclude_dates',
        'event_holidays',
        'event_holiday_weeks',
        'event_holiday_exclude_dates',
        'event_smart_locks',
        'words',
        'news',
        'news_authorities',
        'zoom_connect_users',
        'organizers',
        'admin_authorities',
        'reception_statuses',
        'recaptcha_settings',
    ];

    /**
     * @var array
     */
    protected $transactionTables = [
        'admin_operational_logs',
        'admin_pass_reset_tokens',
        'optin_tokens',
        'users',
        'user_additions',
        'user_login_histories',
        'user_password_reminder_tokens',
        'user_smart_locks',
        'reservations',
        'reservation_additions',
        'reservation_event_plans',
        'reservation_options',
        'reservation_payments',
        'reservation_guest_codes',
        'reservation_video_meetings',
        'reservation_smart_locks',
        'waiting_cancellations',
        'waiting_cancellation_conf_tokens',
        'auto_reply_mail_histories',
        'mail_deliveries',
        'mail_delivery_histories',
        'bounce_mails',
        'bounce_mail_histories',
        'inquiries',
        'temp_access_summaries',
        'access_summaries',
        'admin_sessions',
        'user_sessions',
        'app_access_tokens',
        'payment_errors',
    ];

    /**
     * @var array
     */
    protected $noSequenceTables = [
        'admin_sessions',
        'user_sessions',
    ];

    /**
     * @var string
     */
    protected $dataDirectory = TMP . 'migrate';

    /**
     * @var string
     */
    protected $dumpFileName = 'dump.sql';

    /**
     * マスタテーブルを取得
     *
     * @return array
     */
    protected function getMasterTables()
    {
        return $this->masterTables;
    }

    /**
     * トランザクションテーブルを取得
     *
     * @return array
     */
    protected function getTransactionTables()
    {
        return $this->transactionTables;
    }

    /**
     * シーケンスの存在しないテーブルを取得
     *
     * @return array
     */
    protected function getNoSequenceTables()
    {
        return $this->noSequenceTables;
    }

    /**
     * データディレクトリを取得
     *
     * @return string
     */
    protected function getDataDirectory()
    {
        return $this->dataDirectory;
    }

    /**
     * ダンプファイル名を取得
     *
     * @return string
     */
    protected function getDumpFileName()
    {
        return $this->dumpFileName;
    }
}
