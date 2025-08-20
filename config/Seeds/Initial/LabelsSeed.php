<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * LabelsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class LabelsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'labels';
    protected const TABLE_COLUMNS = [
        'name',
        'sort_no',
        'public_flg',
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
