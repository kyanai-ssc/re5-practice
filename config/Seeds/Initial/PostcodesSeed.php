<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * PostcodesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class PostcodesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'postcodes';
    protected const TABLE_COLUMNS = [
        'zip_code',
        'state_id',
        'state',
        'state_kana',
        'city',
        'city_kana',
        'address',
        'address_kana',
        'company',
        'company_kana',
    ];
    protected const INSERT_COUNT = 5000;

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->checkClient();

        $csvFile = __DIR__ . DS . 'csv' . DS . static::TABLE_NAME . '.csv';
        $options = [
            'ignoreHeader' => false,
            'count' => static::INSERT_COUNT,
        ];

        $table = $this->table(static::TABLE_NAME);
        $this->deleteData(static::TABLE_NAME);
        foreach ($this->readCsvEach($csvFile, static::TABLE_COLUMNS, $options) as $data) {
            $table->insert($data)->save();
        }
        $this->setSequence(static::TABLE_NAME);
    }
}
