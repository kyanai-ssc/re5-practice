<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * AutoReplyMailStatusesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class AutoReplyMailStatusesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'auto_reply_mail_statuses';
    protected const TABLE_COLUMNS = [
        'id',
        'auto_reply_mail_id',
        'reservation_status_from_id',
        'reservation_status_to_id',
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
