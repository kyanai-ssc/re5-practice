<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * PrefecturesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class PrefecturesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'prefectures';
    protected const TABLE_COLUMNS = [
        'id',
        'code',
        'name',
        'default_name',
        'sort_no',
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
