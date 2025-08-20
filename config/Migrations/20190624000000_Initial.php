<?php
declare(strict_types=1);

use App\Model\Entity\AdminAuthority;
use Migrations\AbstractMigration;

/**
 * Initial class.
 *
 * @psalm-suppress UnusedClass
 */
class Initial extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up()
    {
        $this->table('labels', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('parent_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'parent_id',
                ],
                ['name' => 'labels_parent_id']
            )
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'labels_sort_no']
            )
            ->create();

        $this->table('admins', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('label_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('authority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('login_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('password_modify_timestamp', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('password_reset_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('initial_admin_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('system_admin_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('initial_password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('admin_authority_id', 'biginteger', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'login_id',
                ],
                ['unique' => true, 'name' => 'admins_login_id']
            )
            ->addIndex(
                [
                    'label_id',
                ],
                ['name' => 'admins_label_id']
            )
            ->create();

        $this->table('admin_mails', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                ],
                ['name' => 'admin_mails_admin_id']
            )
            ->create();

        $this->table('admin_login_histories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('last_login_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('error_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('last_error_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lock_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                ],
                ['unique' => true, 'name' => 'admin_login_histories_admin_id']
            )
            ->create();

        $this->table('admin_search_items', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('items', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                    'type',
                ],
                ['unique' => true, 'name' => 'admin_search_items_admin_id_type']
            )
            ->create();

        $this->table('admin_list_items', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('items', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                    'type',
                ],
                ['unique' => true, 'name' => 'admin_list_items_admin_id_type']
            )
            ->create();

        $this->table('admin_operational_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('login_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('operated_function', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('operated_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('operated_data', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('edited_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                ],
                ['name' => 'admin_operational_logs_admin_id']
            )
            ->create();

        $this->table('admin_pass_reset_tokens', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'token',
                ],
                ['unique' => true, 'name' => 'admin_pass_reset_tokens_token']
            )
            ->addIndex(
                [
                    'admin_id',
                ],
                ['name' => 'admin_pass_reset_tokens_admin_id']
            )
            ->create();

        $this->table('site_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('login_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('login_required_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('user_add_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('user_edit_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('mail_edit_optin_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('optin_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('id_reminder_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('password_reminder_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_add_user_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_add_not_user_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_continuous_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_reminder_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_reminder_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_reminder_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('inquiry_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('front_public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('site_theme', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('key_visual_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('top_information', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('header_logo_pc', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('header_logo_sp', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('login_display_add_user_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('top_news_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('top_search_label_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('top_search_tag_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('tag_search_method', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('top_search_event_name_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('calendar_search_label_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('calendar_search_tag_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('calendar_search_event_name_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('calendar_date_default', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('calendar_time_from', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('calendar_time_to', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('calendar_time_default', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('calendar_month_display_limit', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('reservation_form_type_first', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('reservation_edit_event_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('terms_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('user_terms_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_terms_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('sctl_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_sctl_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('charge_breakdown_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('news_new_period_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('news_new_period_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('meta_keyword', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('admin_calendar_type_default', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('reservation_edit_not_user_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_close_reminder_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_close_reminder_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_close_reminder_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_edit_payment_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('calendar_registration_deadline_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('file_groups', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('directory', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'directory',
                ],
                ['unique' => true, 'name' => 'file_groups_directory']
            )
            ->create();

        $this->table('files', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('file_group_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('file_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('file_type', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'file_group_id',
                    'file_name',
                    'file_type',
                ],
                ['unique' => true, 'name' => 'files_file_group_id_file_name_file_type']
            )
            ->create();

        $this->table('terms', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'type',
                ],
                ['unique' => true, 'name' => 'terms_type']
            )
            ->create();

        $this->table('analysis_tags', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'type',
                ],
                ['unique' => true, 'name' => 'analysis_tags_type']
            )
            ->create();

        $this->table('prefectures', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('default_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'code',
                ],
                ['unique' => true, 'name' => 'prefectures_code']
            )
            ->addIndex(
                [
                    'sort_no',
                ],
                ['unique' => true, 'name' => 'prefectures_sort_no']
            )
            ->create();

        $this->table('reservation_statuses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('default_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('status_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('keep_stock_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('search_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'reservation_statuses_sort_no']
            )
            ->create();

        $this->table('color_chips', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('color_code', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('front_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'color_chips_sort_no']
            )
            ->create();

        $this->table('payment_methods', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['unique' => true, 'name' => 'payment_methods_sort_no']
            )
            ->create();

        $this->table('payment_statuses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['unique' => true, 'name' => 'payment_statuses_sort_no']
            )
            ->create();

        $this->table('options', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('usage_timestamp_from', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('usage_timestamp_to', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('stock', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('stock_unit', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('charge', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('option_stock_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('option_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('usage_timestamp_from', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('usage_timestamp_to', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('stock', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'option_id',
                ],
                ['name' => 'option_stock_settings_option_id']
            )
            ->create();

        $this->table('form_groups', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('smart_lock_type', 'smallinteger', [
                'default' => null,
                'limit' => 5,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'form_groups_sort_no']
            )
            ->create();

        $this->table('form_items', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_group_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('input_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('required_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('smart_lock_type', 'smallinteger', [
                'default' => null,
                'limit' => 5,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_group_id',
                    'sort_no',
                ],
                ['name' => 'form_items_form_group_id_sort_no']
            )
            ->create();

        $this->table('form_item_details', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('front_word', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('back_word', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('text_lower_limit', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('text_upper_limit', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('text_input_translate', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('text_input_check', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('date_lower_limit', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('date_upper_limit_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('date_upper_limit_absolute', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('date_upper_limit_relative', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('date_default', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_item_id',
                    'sort_no',
                ],
                ['name' => 'form_item_details_form_item_id_sort_no']
            )
            ->create();

        $this->table('form_item_choices', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_item_id',
                    'sort_no',
                ],
                ['name' => 'form_item_choices_form_item_id_sort_no']
            )
            ->create();

        $this->table('form_item_option_groups', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('select_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['unique' => true, 'name' => 'form_item_option_groups_form_item_id']
            )
            ->create();

        $this->table('form_item_options', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_option_group_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('option_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('stock_range_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('stock_range_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_item_option_group_id',
                    'option_id',
                ],
                ['name' => 'form_item_options_form_item_option_group_id_option_id']
            )
            ->addIndex(
                [
                    'form_item_option_group_id',
                    'sort_no',
                ],
                ['name' => 'form_item_options_form_item_option_group_id_sort_no']
            )
            ->addIndex(
                [
                    'option_id',
                ],
                ['name' => 'form_item_options_option_id']
            )
            ->create();

        $this->table('form_patterns', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('remark', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('form_pattern_display_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_pattern_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('display_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('app_display_flg', 'smallinteger', [
                'after' => 'modified',
                'default' => 0,
                'length' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_pattern_id',
                    'form_item_id',
                ],
                ['unique' => true, 'name' => 'form_pattern_display_types_form_pattern_id_form_item_id']
            )
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'form_pattern_display_types_form_item_id']
            )
            ->create();

        $this->table('form_pattern_options', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_pattern_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_option_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_pattern_id',
                    'form_item_option_id',
                ],
                ['unique' => true, 'name' => 'form_pattern_options_form_pattern_id_form_item_option_id']
            )
            ->addIndex(
                [
                    'form_item_option_id',
                ],
                ['name' => 'form_pattern_options_form_item_option_id']
            )
            ->create();

        $this->table('user_authorities', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('access', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('form_pattern_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('calendar_type', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('calendar_type_default', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('login_name_form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('reservation_limit_all', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_future', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_month', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('guest_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_pattern_id',
                ],
                ['name' => 'user_authorities_form_pattern_id']
            )
            ->addIndex(
                [
                    'login_name_form_item_id',
                ],
                ['name' => 'user_authorities_login_name_form_item_id']
            )
            ->create();

        $this->table('tag_groups', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'tag_groups_sort_no']
            )
            ->create();

        $this->table('tags', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('tag_group_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'tag_group_id',
                    'sort_no',
                ],
                ['name' => 'tags_tag_group_id_sort_no']
            )
            ->create();

        $this->table('auto_reply_mails', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_authority_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('label_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('except_sub_label_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('from_mail_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('from_mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('reply_to', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('content_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('subject', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('header', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('footer', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('admin_operation_mail_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('header_admin', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('contents_admin', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('footer_admin', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_authority_id',
                ],
                ['name' => 'auto_reply_mails_user_authority_id']
            )
            ->addIndex(
                [
                    'label_id',
                ],
                ['name' => 'auto_reply_mails_label_id']
            )
            ->create();

        $this->table('auto_reply_mail_statuses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('auto_reply_mail_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_status_from_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('reservation_status_to_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'auto_reply_mail_id',
                    'reservation_status_from_id',
                    'reservation_status_to_id',
                ],
                ['unique' => true, 'name' => 'auto_reply_mail_statuses_auto_reply_mail_id_status_id']
            )
            ->addIndex(
                [
                    'reservation_status_from_id',
                ],
                ['name' => 'auto_reply_mail_statuses_reservation_status_from_id']
            )
            ->addIndex(
                [
                    'reservation_status_to_id',
                ],
                ['name' => 'auto_reply_mail_statuses_reservation_status_to_id']
            )
            ->create();

        $this->table('holidays', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'date',
                ],
                ['unique' => true, 'name' => 'holidays_date']
            )
            ->create();

        $this->table('events', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('label_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('date_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_to', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_from', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('time_to', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('event_unit_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('time_plan', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('multiple_time_plan_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_unit_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_time_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_time_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_unit_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_day_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_day_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('usage_time_notation', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('interval_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('interval_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('charge', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('stock', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('stock_range_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('stock_range_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('stock_unit', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('stock_display_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('public_from', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('public_to', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reception_period_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reception_period_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('registration_deadline_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('registration_deadline_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('registration_deadline_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('editing_deadline_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('editing_deadline_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('editing_deadline_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cancellation_deadline_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('cancellation_deadline_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('cancellation_deadline_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reservation_status_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('waiting_cancellation_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('reservation_limit_all', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_future', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_month', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('reservation_limit_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('duplication_check_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('background_color_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('color_chip_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('background_color_replace_front', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('background_color_replace_admin', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('format_type_display', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('form_pattern_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('organizer_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('qr_code_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('registration_deadline_criterion', 'smallinteger', [
                'default' => 1,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('editing_deadline_criterion', 'smallinteger', [
                'default' => 1,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('cancellation_deadline_criterion', 'smallinteger', [
                'default' => 1,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'label_id',
                ],
                ['name' => 'events_label_id']
            )
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'events_sort_no']
            )
            ->addIndex(
                [
                    'date_from',
                ],
                ['name' => 'events_date_from']
            )
            ->addIndex(
                [
                    'date_to',
                ],
                ['name' => 'events_date_to']
            )
            ->addIndex(
                [
                    'time_from',
                ],
                ['name' => 'events_time_from']
            )
            ->addIndex(
                [
                    'time_to',
                ],
                ['name' => 'events_time_to']
            )
            ->addIndex(
                [
                    'public_from',
                ],
                ['name' => 'events_public_from']
            )
            ->addIndex(
                [
                    'public_to',
                ],
                ['name' => 'events_public_to']
            )
            ->addIndex(
                [
                    'reservation_status_id',
                ],
                ['name' => 'events_reservation_status_id']
            )
            ->addIndex(
                [
                    'color_chip_id',
                ],
                ['name' => 'events_color_chip_id']
            )
            ->addIndex(
                [
                    'form_pattern_id',
                ],
                ['name' => 'events_form_pattern_id']
            )
            ->addIndex(
                [
                    'organizer_id',
                ],
                ['name' => 'events_organizer_id']
            )
            ->create();

        $this->table('event_tags', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('tag_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'tag_id',
                ],
                ['unique' => true, 'name' => 'event_tags_event_id_tag_id']
            )
            ->addIndex(
                [
                    'tag_id',
                ],
                ['name' => 'event_tags_tag_id']
            )
            ->create();

        $this->table('event_weeks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('week', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'week',
                ],
                ['unique' => true, 'name' => 'event_weeks_event_id_week']
            )
            ->create();

        $this->table('event_plans', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('usage_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('usage_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('charge', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('public_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'sort_no',
                ],
                ['name' => 'event_plans_event_id_sort_no']
            )
            ->create();

        $this->table('event_stock_marks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('symbolic', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'number',
                ],
                ['unique' => true, 'name' => 'event_stock_marks_event_id_number']
            )
            ->create();

        $this->table('event_remarks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('detail_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('remark', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'form_item_id',
                ],
                ['unique' => true, 'name' => 'event_remarks_event_id_form_item_id']
            )
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'event_remarks_form_item_id']
            )
            ->create();

        $this->table('event_images', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                    'sort_no',
                ],
                ['name' => 'event_images_event_id_sort_no']
            )
            ->create();

        $this->table('event_stock_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('date_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('date_to', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_from', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_to', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('stock', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                ],
                ['name' => 'event_stock_settings_event_id']
            )
            ->addIndex(
                [
                    'date_from',
                ],
                ['name' => 'event_stock_settings_date_from']
            )
            ->addIndex(
                [
                    'date_to',
                ],
                ['name' => 'event_stock_settings_date_to']
            )
            ->create();

        $this->table('event_stock_setting_weeks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_stock_setting_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('week', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_stock_setting_id',
                    'week',
                ],
                ['unique' => true, 'name' => 'event_stock_setting_weeks_event_stock_setting_id_week']
            )
            ->create();

        $this->table('event_stock_setting_exclude_dates', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_stock_setting_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_stock_setting_id',
                ],
                ['name' => 'event_stock_setting_exclude_dates_event_stock_setting_id']
            )
            ->create();

        $this->table('event_holidays', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('date_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('date_to', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_from', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_to', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                ],
                ['name' => 'event_holidays_event_id']
            )
            ->addIndex(
                [
                    'date_from',
                ],
                ['name' => 'event_holidays_date_from']
            )
            ->addIndex(
                [
                    'date_to',
                ],
                ['name' => 'event_holidays_date_to']
            )
            ->create();

        $this->table('event_holiday_weeks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_holiday_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('week', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_holiday_id',
                    'week',
                ],
                ['unique' => true, 'name' => 'event_holiday_weeks_event_holiday_id_week']
            )
            ->create();

        $this->table('event_holiday_exclude_dates', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_holiday_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_holiday_id',
                ],
                ['name' => 'event_holiday_exclude_dates_event_holiday_id']
            )
            ->create();

        $this->table('words', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('category', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('function', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('translate_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('word', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('word_default', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('multiple_row_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('admin_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'type',
                    'category',
                    'function',
                    'code',
                ],
                ['unique' => true, 'name' => 'words_type_category_function_code']
            )
            ->addIndex(
                [
                    'translate_key',
                ],
                ['unique' => true, 'name' => 'words_translate_key']
            )
            ->create();

        $this->table('news', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('label_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('title', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('public_from', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('public_to', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'label_id',
                ],
                ['name' => 'news_label_id']
            )
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'news_sort_no']
            )
            ->create();

        $this->table('news_authorities', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('news_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_authority_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'news_id',
                    'user_authority_id',
                ],
                ['unique' => true, 'name' => 'news_authorities_news_id_user_authority_id']
            )
            ->addIndex(
                [
                    'user_authority_id',
                ],
                ['name' => 'news_authorities_user_authority_id']
            )
            ->create();

        $this->table('optin_tokens', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'token',
                ],
                ['unique' => true, 'name' => 'optin_tokens_token']
            )
            ->addIndex(
                [
                    'mail',
                ],
                ['name' => 'optin_tokens_mail']
            )
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'optin_tokens_user_id']
            )
            ->create();

        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_authority_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('login_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('guest_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('withdrawal_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('password_modify_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expiration_date_from', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expiration_date_to', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_authority_id',
                ],
                ['name' => 'users_user_authority_id']
            )
            ->addIndex(
                [
                    'login_id',
                ],
                ['name' => 'users_login_id']
            )
            ->addIndex(
                [
                    'mail',
                ],
                ['name' => 'users_mail']
            )
            ->addIndex(
                [
                    'expiration_date_from',
                ],
                ['name' => 'users_expiration_date_from']
            )
            ->addIndex(
                [
                    'expiration_date_to',
                ],
                ['name' => 'users_expiration_date_to']
            )
            ->create();

        $this->table('user_additions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                    'form_item_id',
                ],
                ['unique' => true, 'name' => 'user_additions_user_id_form_item_id']
            )
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'user_additions_form_item_id']
            )
            ->create();

        $this->table('user_login_histories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('last_login_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('error_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('last_error_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lock_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['unique' => true, 'name' => 'user_login_histories_user_id']
            )
            ->create();

        $this->table('user_password_reminder_tokens', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'token',
                ],
                ['unique' => true, 'name' => 'user_password_reminder_tokens_token']
            )
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'user_password_reminder_tokens_user_id']
            )
            ->create();

        $this->table('reservations', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('usage_timestamp_from', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('usage_timestamp_to', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('usage_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('usage_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('charge', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('reservation_status_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('payment_method_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('payment_status_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('cancel_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('qr_code', 'text', [
                'after' => 'modified',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('reception_status_id', 'biginteger', [
                'after' => 'qr_code',
                'default' => null,
                'length' => 20,
                'null' => true,
            ])
            ->addColumn('reception_timestamp', 'timestamp', [
                'after' => 'reception_status_id',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'reservations_user_id']
            )
            ->addIndex(
                [
                    'event_id',
                ],
                ['name' => 'reservations_event_id']
            )
            ->addIndex(
                [
                    'usage_timestamp_from',
                ],
                ['name' => 'reservations_usage_timestamp_from']
            )
            ->addIndex(
                [
                    'usage_timestamp_to',
                ],
                ['name' => 'reservations_usage_timestamp_to']
            )
            ->addIndex(
                [
                    'reservation_status_id',
                ],
                ['name' => 'reservations_reservation_status_id']
            )
            ->addIndex(
                [
                    'payment_method_id',
                ],
                ['name' => 'reservations_payment_method_id']
            )
            ->addIndex(
                [
                    'payment_status_id',
                ],
                ['name' => 'reservations_payment_status_id']
            )
            ->addIndex(
                [
                    'qr_code',
                ],
                ['unique' => true, 'name' => 'reservations_qr_code']
            )
            ->create();

        $this->table('reservation_additions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'reservation_id',
                    'form_item_id',
                ],
                ['unique' => true, 'name' => 'reservation_additions_reservation_id_form_item_id']
            )
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'reservation_additions_form_item_id']
            )
            ->create();

        $this->table('reservation_event_plans', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_plan_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'reservation_id',
                    'event_plan_id',
                ],
                ['unique' => true, 'name' => 'reservation_event_plans_reservation_id_event_plan_id']
            )
            ->addIndex(
                [
                    'event_plan_id',
                ],
                ['name' => 'reservation_event_plans_event_plan_id']
            )
            ->create();

        $this->table('reservation_options', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('option_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'reservation_id',
                    'option_id',
                ],
                ['unique' => true, 'name' => 'reservation_options_reservation_id_option_id']
            )
            ->addIndex(
                [
                    'option_id',
                ],
                ['name' => 'reservation_options_option_id']
            )
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'reservation_options_form_item_id']
            )
            ->create();

        $this->table('reservation_payments', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('charge', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('payment_order_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_service', 'integer', [
                'default' => null,
                'length' => 10,
                'null' => false,
            ])
            ->addColumn('payment_method_id', 'biginteger', [
                'default' => null,
                'length' => 20,
                'null' => false,
            ])
            ->addColumn('payment_limit', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('status', 'integer', [
                'default' => null,
                'length' => 10,
                'null' => false,
            ])
            ->addColumn('access_id', 'text', [
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('access_pass', 'text', [
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('payment_tran_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_tracking_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_process_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payment_cancel_date', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('receive_result_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'payment_order_id',
                ],
                ['unique' => true, 'name' => 'reservation_payments_payment_order_id']
            )
            ->addIndex(
                [
                    'reservation_id',
                ],
                ['name' => 'reservation_payments_reservation_id']
            )
            ->addIndex(
                [
                    'payment_method_id',
                ],
                ['name' => 'reservation_payments_payment_method_id']
            )
            ->addIndex(
                [
                    'access_id',
                ],
                [
                    'name' => 'reservation_payments_access_id',
                ]
            )
            ->create();

        $this->table('reservation_guest_codes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('code', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'code',
                ],
                ['unique' => true, 'name' => 'reservation_guest_codes_code']
            )
            ->addIndex(
                [
                    'reservation_id',
                ],
                ['name' => 'reservation_guest_codes_reservation_id']
            )
            ->create();

        $this->table('waiting_cancellations', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('usage_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('notify_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'waiting_cancellations_user_id']
            )
            ->addIndex(
                [
                    'event_id',
                ],
                ['name' => 'waiting_cancellations_event_id']
            )
            ->addIndex(
                [
                    'usage_timestamp',
                ],
                ['name' => 'waiting_cancellations_usage_timestamp']
            )
            ->addIndex(
                [
                    'mail',
                ],
                ['name' => 'waiting_cancellations_mail']
            )
            ->create();

        $this->table('waiting_cancellation_conf_tokens', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'token',
                ],
                ['unique' => true, 'name' => 'waiting_cancellation_conf_tokens_token']
            )
            ->addIndex(
                [
                    'mail',
                ],
                ['name' => 'waiting_cancellation_conf_tokens_mail']
            )
            ->create();

        $this->table('auto_reply_mail_histories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('auto_reply_mail_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('waiting_cancellation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('user_mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('send_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('send_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('reminder_setting_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('bounce_mail_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('data', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'bounce_mail_token',
                ],
                ['unique' => true, 'name' => 'auto_reply_mail_histories_bounce_mail_token']
            )
            ->addIndex(
                [
                    'auto_reply_mail_id',
                ],
                ['name' => 'auto_reply_mail_histories_auto_reply_mail_id']
            )
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'auto_reply_mail_histories_user_id']
            )
            ->addIndex(
                [
                    'reservation_id',
                ],
                ['name' => 'auto_reply_mail_histories_reservation_id']
            )
            ->addIndex(
                [
                    'waiting_cancellation_id',
                ],
                ['name' => 'auto_reply_mail_histories_waiting_cancellation_id']
            )
            ->addIndex(
                [
                    'admin_id',
                ],
                ['name' => 'auto_reply_mail_histories_admin_id']
            )
            ->create();

        $this->table('mail_deliveries', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('send_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('send_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('send_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('from_mail_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('from_mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('reply_to', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('content_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('subject', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('delivery_target', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('send_status', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('send_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('mail_delivery_histories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail_delivery_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('send_status', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('bounce_mail_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'bounce_mail_token',
                ],
                ['unique' => true, 'name' => 'mail_delivery_histories_bounce_mail_token']
            )
            ->addIndex(
                [
                    'mail_delivery_id',
                    'user_id',
                ],
                ['unique' => true, 'name' => 'mail_delivery_histories_mail_delivery_id_user_id']
            )
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'mail_delivery_histories_user_id']
            )
            ->create();

        $this->table('bounce_mails', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('total_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('remaining_number', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('send_exclude_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'mail',
                ],
                ['unique' => true, 'name' => 'bounce_mails_mail']
            )
            ->create();

        $this->table('bounce_mail_histories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('bounce_mail_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'bounce_mail_id',
                ],
                ['name' => 'bounce_mail_histories_bounce_mail_id']
            )
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'bounce_mail_histories_user_id']
            )
            ->create();

        $this->table('inquiries', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('phone_number', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('mail', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('contents', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('mail_send_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'inquiries_user_id']
            )
            ->create();

        $this->table('temp_access_summaries', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('access_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('calendar', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'access_date',
                ],
                ['name' => 'temp_access_summaries_access_date']
            )
            ->create();

        $this->table('access_summaries', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('access_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('users', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservations', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('calendar', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'access_date',
                ],
                ['unique' => true, 'name' => 'access_summaries_access_date']
            )
            ->create();

        $this->table('system_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('contract_plan', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('footer_logo_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('payment_use_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('admin_password_reset_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('smart_lock_use_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('payment_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('payment_service', 'integer', [
                'default' => null,
                'length' => 10,
                'null' => false,
            ])
            ->addColumn('environment', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('shop_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('merchant_id', 'text', [
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('service_id', 'text', [
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('cust_code_prefix', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('order_id_prefix', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('job_code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('three_d_secure_flg', 'smallinteger', [
                'default' => 0,
                'length' => 5,
                'null' => false,
            ])
            ->addColumn('card_brand', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('shop_password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('hash_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('basic_auth_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('basic_auth_password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('encrypt_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('encrypt_iv', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'shop_id',
                ],
                ['unique' => true, 'name' => 'payment_settings_shop_id']
            )
            ->addIndex(
                [
                    'merchant_id',
                    'service_id',
                ],
                ['unique' => true, 'name' => 'payment_settings_merchant_id_service_id']
            )
            ->create();

        $this->table('postcodes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('zip_code', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('state_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('state', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('state_kana', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('city', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('city_kana', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('address', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('address_kana', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('company', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('company_kana', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'zip_code',
                ],
                ['name' => 'postcodes_zip_code']
            )
            ->create();

        $this->table('admin_sessions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('data', 'binary', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expires', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('user_sessions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('data', 'binary', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expires', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('zoom_connect_users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('refresh_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('access_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('at_expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('organizers', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('video_meeting_type', 'integer', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('zoom_api_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('zoom_api_secret', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('zoom_connect_type', 'smallinteger', [
                'default' => 1,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('zoom_host_email', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meet_api_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meet_calendar_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('sort_key', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_key',
                ],
                ['name' => 'organizers_sort_key']
            )
            ->create();

        $this->table('reservation_video_meetings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('organizer_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('video_meeting_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('video_meeting_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('video_meeting_password', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('video_meeting_type', 'integer', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'reservation_id',
                ],
                ['name' => 'reservation_video_meetings_reservation_id']
            )
            ->addIndex(
                [
                    'organizer_id',
                ],
                ['name' => 'reservation_video_meetings_organizer_id']
            )
            ->create();

        $this->table('admin_authorities', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('access_setting', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('access_operator', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('default_flg', 'smallinteger', [
                'default' => AdminAuthority::DEFAULT_FLG_OFF,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('app_access_tokens', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('admin_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'admin_id',
                ],
                ['name' => 'app_access_tokens_admin_id']
            )
            ->addIndex(
                [
                    'token',
                ],
                ['unique' => true, 'name' => 'app_access_tokens_token']
            )
            ->create();

        $this->table('app_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('api_secret', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'api_secret',
                ],
                ['unique' => true, 'name' => 'app_settings_api_secret']
            )
            ->create();

        $this->table('reception_statuses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('default_name', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_no', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('status_type', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('search_display_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'sort_no',
                ],
                ['name' => 'reception_statuses_sort_no']
            )
            ->create();

        $this->table('reservation_smart_locks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reservation_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('smart_lock_user_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('smart_lock_grant_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('smart_lock_pin', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('smart_lock_key_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('smart_lock_key_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'reservation_id',
                ],
                ['name' => 'reservation_smart_locks_reservation_id']
            )
            ->addIndex(
                [
                    'smart_lock_user_id',
                ],
                ['name' => 'reservation_smart_locks_smart_lock_user_id']
            )
            ->create();

        $this->table('event_smart_locks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('event_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('smart_lock_device_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('smart_lock_key_url_flg', 'smallinteger', [
                'default' => null,
                'limit' => 5,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'event_id',
                ],
                ['name' => 'event_smart_locks_event_id']
            )
            ->create();

        $this->table('smart_locks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('type', 'smallinteger', [
                'default' => null,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('client_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('client_secret', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('buffer', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('organizations_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expires', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('refresh_token', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('form_item_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('app_use_flg', 'smallinteger', [
                'default' => 1,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'form_item_id',
                ],
                ['name' => 'smart_locks_form_item_id']
            )
            ->create();

        $this->table('user_smart_locks', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('smart_lock_user_id', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                ['name' => 'user_smart_locks_user_id']
            )
            ->addIndex(
                [
                    'smart_lock_user_id',
                ],
                ['name' => 'user_smart_locks_smart_lock_user_id']
            )
            ->create();

        $this->table('recaptcha_settings', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('use_flg', 'smallinteger', [
                'default' => 0,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('site_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('secret_key', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('payment_errors', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('ip_address', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('error_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('error_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lock_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('last_error_timestamp', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('all_error_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down()
    {
        //$this->table('admin_authorities')->drop()->save();
        //$this->table('reception_statuses')->drop()->save();
        //$this->table('app_settings')->drop()->save();
        //$this->table('app_access_tokens')->drop()->save();
        //$this->table('reservation_video_meetings')->drop()->save();
        //$this->table('organizers')->drop()->save();
        //$this->table('zoom_connect_users')->drop()->save();
        //$this->table('user_sessions')->drop()->save();
        //$this->table('admin_sessions')->drop()->save();
        //$this->table('postcodes')->drop()->save();
        //$this->table('payment_settings')->drop()->save();
        //$this->table('system_settings')->drop()->save();
        //$this->table('access_summaries')->drop()->save();
        //$this->table('temp_access_summaries')->drop()->save();
        //$this->table('inquiries')->drop()->save();
        //$this->table('bounce_mail_histories')->drop()->save();
        //$this->table('bounce_mails')->drop()->save();
        //$this->table('mail_delivery_histories')->drop()->save();
        //$this->table('mail_deliveries')->drop()->save();
        //$this->table('auto_reply_mail_histories')->drop()->save();
        //$this->table('waiting_cancellation_conf_tokens')->drop()->save();
        //$this->table('waiting_cancellations')->drop()->save();
        //$this->table('reservation_guest_codes')->drop()->save();
        //$this->table('reservation_payments')->drop()->save();
        //$this->table('reservation_options')->drop()->save();
        //$this->table('reservation_event_plans')->drop()->save();
        //$this->table('reservation_additions')->drop()->save();
        //$this->table('reservations')->drop()->save();
        //$this->table('user_password_reminder_tokens')->drop()->save();
        //$this->table('user_login_histories')->drop()->save();
        //$this->table('user_additions')->drop()->save();
        //$this->table('users')->drop()->save();
        //$this->table('optin_tokens')->drop()->save();
        //$this->table('news_authorities')->drop()->save();
        //$this->table('news')->drop()->save();
        //$this->table('words')->drop()->save();
        //$this->table('event_holiday_exclude_dates')->drop()->save();
        //$this->table('event_holiday_weeks')->drop()->save();
        //$this->table('event_holidays')->drop()->save();
        //$this->table('event_stock_setting_exclude_dates')->drop()->save();
        //$this->table('event_stock_setting_weeks')->drop()->save();
        //$this->table('event_stock_settings')->drop()->save();
        //$this->table('event_images')->drop()->save();
        //$this->table('event_remarks')->drop()->save();
        //$this->table('event_stock_marks')->drop()->save();
        //$this->table('event_plans')->drop()->save();
        //$this->table('event_weeks')->drop()->save();
        //$this->table('event_tags')->drop()->save();
        //$this->table('events')->drop()->save();
        //$this->table('holidays')->drop()->save();
        //$this->table('auto_reply_mail_statuses')->drop()->save();
        //$this->table('auto_reply_mails')->drop()->save();
        //$this->table('tags')->drop()->save();
        //$this->table('tag_groups')->drop()->save();
        //$this->table('user_authorities')->drop()->save();
        //$this->table('form_pattern_options')->drop()->save();
        //$this->table('form_pattern_display_types')->drop()->save();
        //$this->table('form_patterns')->drop()->save();
        //$this->table('form_item_options')->drop()->save();
        //$this->table('form_item_option_groups')->drop()->save();
        //$this->table('form_item_choices')->drop()->save();
        //$this->table('form_item_details')->drop()->save();
        //$this->table('form_items')->drop()->save();
        //$this->table('form_groups')->drop()->save();
        //$this->table('option_stock_settings')->drop()->save();
        //$this->table('options')->drop()->save();
        //$this->table('payment_statuses')->drop()->save();
        //$this->table('payment_methods')->drop()->save();
        //$this->table('color_chips')->drop()->save();
        //$this->table('reservation_statuses')->drop()->save();
        //$this->table('prefectures')->drop()->save();
        //$this->table('analysis_tags')->drop()->save();
        //$this->table('terms')->drop()->save();
        //$this->table('files')->drop()->save();
        //$this->table('file_groups')->drop()->save();
        //$this->table('site_settings')->drop()->save();
        //$this->table('admin_pass_reset_tokens')->drop()->save();
        //$this->table('admin_operational_logs')->drop()->save();
        //$this->table('admin_list_items')->drop()->save();
        //$this->table('admin_search_items')->drop()->save();
        //$this->table('admin_login_histories')->drop()->save();
        //$this->table('admin_mails')->drop()->save();
        //$this->table('admins')->drop()->save();
        //$this->table('labels')->drop()->save();
        //$this->table('reservation_smart_locks')->drop()->save();
        //$this->table('event_smart_locks')->drop()->save();
        //$this->table('smart_locks')->drop()->save();
        //$this->table('user_smart_locks')->drop()->save();
        //$this->table('recaptcha_settings')->drop()->save();
        //$this->table('payment_errors')->drop()->save();
    }
}
