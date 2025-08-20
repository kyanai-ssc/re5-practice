<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * FormGroupsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class FormGroupsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'form_groups';
    protected const TABLE_COLUMNS = [
        'id',
        'form_type',
        'name',
        'name_display_flg',
        'sort_no',
        'smart_lock_type',
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
