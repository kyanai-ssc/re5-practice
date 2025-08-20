<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * FormItemDetailsSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class FormItemDetailsSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'form_item_details';
    protected const TABLE_COLUMNS = [
        'id',
        'form_item_id',
        'front_word',
        'back_word',
        'text_lower_limit',
        'text_upper_limit',
        'text_input_translate',
        'text_input_check',
        'date_lower_limit',
        'date_upper_limit_type',
        'date_upper_limit_absolute',
        'date_upper_limit_relative',
        'date_default',
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
