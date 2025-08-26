<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * FormItemsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class FormItemsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'form_items';
    protected const TABLE_COLUMNS = [
        'id',
        'form_group_id',
        'input_type',
        'name',
        'required_flg',
        'reservation_display_flg',
        'description',
        'sort_no',
        'default_flg',
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
