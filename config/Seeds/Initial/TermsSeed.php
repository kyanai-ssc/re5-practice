<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * TermsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class TermsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'terms';
    protected const TABLE_COLUMNS = [
        'id',
        'type',
        'contents',
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
