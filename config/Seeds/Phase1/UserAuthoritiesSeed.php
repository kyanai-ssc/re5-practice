<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;

/**
 * UserAuthoritiesSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class UserAuthoritiesSeed extends AbstractSeed
{
    protected const TABLE_NAME = 'user_authorities';
    protected const TABLE_COLUMNS = [
        'id',
        'name',
        'access',
        'form_pattern_id',
        'calendar_type',
        'calendar_type_default',
        'login_name_form_item_id',
        'reservation_limit_all',
        'reservation_limit_future',
        'reservation_limit_month',
        'reservation_limit_day',
        'guest_flg',
        'default_flg',
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
