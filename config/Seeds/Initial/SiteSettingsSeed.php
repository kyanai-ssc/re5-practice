<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * SiteSettingsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class SiteSettingsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'site_settings';
    protected const TABLE_COLUMNS = [
        'id',
        'login_flg',
        'login_required_flg',
        'user_add_flg',
        'user_edit_flg',
        'mail_edit_optin_flg',
        'optin_flg',
        'id_reminder_flg',
        'password_reminder_flg',
        'reservation_add_user_flg',
        'reservation_add_not_user_flg',
        'reservation_continuous_flg',
        'reservation_reminder_flg',
        'reservation_reminder_type',
        'reservation_reminder_time',
        'inquiry_flg',
        'front_public_flg',
        'site_theme',
        'key_visual_url',
        'top_information',
        'header_logo_pc',
        'header_logo_sp',
        'login_display_add_user_flg',
        'top_news_number',
        'top_search_label_flg',
        'top_search_tag_flg',
        'tag_search_method',
        'top_search_event_name_flg',
        'calendar_search_label_flg',
        'calendar_search_tag_flg',
        'calendar_search_event_name_flg',
        'calendar_date_default',
        'calendar_time_from',
        'calendar_time_to',
        'calendar_time_default',
        'calendar_month_display_limit',
        'reservation_form_type_first',
        'reservation_edit_event_flg',
        'terms_flg',
        'user_terms_flg',
        'reservation_terms_flg',
        'news_new_period_type',
        'news_new_period_number',
        'meta_keyword',
        'meta_description',
        'admin_calendar_type_default',
        'reservation_edit_not_user_flg',
        'reservation_close_reminder_flg',
        'reservation_close_reminder_type',
        'reservation_close_reminder_time',
        'reservation_edit_payment_flg',
        'calendar_registration_deadline_display_flg',
    ];

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->checkClient();

        $csvFile = __DIR__ . DS . 'csv' . DS . static::TABLE_NAME . '.csv';
        $data = $this->readCsv($csvFile, static::TABLE_COLUMNS);

        $table = $this->table(static::TABLE_NAME);
        $table->insert($data)->save();
        $this->setSequence(static::TABLE_NAME);
    }
}
