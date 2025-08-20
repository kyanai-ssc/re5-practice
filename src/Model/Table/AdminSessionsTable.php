<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;

/**
 * AdminSessions Model
 */
class AdminSessionsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    protected $noAdditionalBehavior = true;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }
}
