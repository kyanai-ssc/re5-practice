<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * FormItemChoicesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class FormItemChoicesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'form_item_choices';
    protected const TABLE_COLUMNS = [
        'id',
        'form_item_id',
        'name',
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