<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * WordsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class WordsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'words';
    protected const TABLE_COLUMNS = [
        'type',
        'category',
        'function',
        'code',
        'translate_key',
        'name',
        'word',
        'word_default',
        'multiple_row_flg',
        'admin_flg',
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
