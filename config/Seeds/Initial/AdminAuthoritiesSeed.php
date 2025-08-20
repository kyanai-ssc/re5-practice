<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * AdminAuthoritiesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class AdminAuthoritiesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'admin_authorities';
    protected const TABLE_COLUMNS = [
        'id',
        'name',
        'access_setting',
        'access_operator',
        'default_flg',
        'created',
        'modified',
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
