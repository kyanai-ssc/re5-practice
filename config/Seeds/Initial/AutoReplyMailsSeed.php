<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * AutoReplyMailsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class AutoReplyMailsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'auto_reply_mails';
    protected const TABLE_COLUMNS = [
        'id',
        'type',
        'user_authority_id',
        'label_id',
        'except_sub_label_flg',
        'from_mail_name',
        'from_mail',
        'reply_to',
        'content_type',
        'subject',
        'header',
        'contents',
        'footer',
        'admin_operation_mail_flg',
        'header_admin',
        'contents_admin',
        'footer_admin',
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
