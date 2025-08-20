<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * SystemSettingsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class SystemSettingsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'system_settings';
    protected const TABLE_COLUMNS = [
        'id',
        'contract_plan',
        'footer_logo_display_flg',
        'payment_use_flg',
        'admin_password_reset_day',
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
