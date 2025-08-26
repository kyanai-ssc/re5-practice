<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * FormPatternDisplayTypesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class FormPatternDisplayTypesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'form_pattern_display_types';
    protected const TABLE_COLUMNS = [
        'id',
        'form_pattern_id',
        'form_item_id',
        'display_type',
        'app_display_flg',
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
