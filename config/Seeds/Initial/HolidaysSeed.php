<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * HolidaysSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class HolidaysSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'holidays';
    protected const TABLE_COLUMNS = [
        'date',
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
