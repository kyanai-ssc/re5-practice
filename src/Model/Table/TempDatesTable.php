<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\Database\Connection;
use Cake\Database\Schema\TableSchema;
use Cake\Utility\Hash;

/**
 * TempDates Model
 */
class TempDatesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        if (!Hash::get($config, 'noCreate', false)) {
            $this->createTempTable();
        }
    }

    /**
     * TemporaryTable Create
     *
     * @return void
     * @throws \Exception
     */
    public function createTempTable()
    {
        $schema = new TableSchema('temp_dates');
        $schema
            ->addColumn('date', ['type' => TableSchema::TYPE_DATE])
            ->setTemporary(true);

        $this->setSchema($schema);

        $connection = $this->getConnection();
        $queries = $schema->createSql($connection);

        $connection->transactional(
            function (Connection $connection) use ($queries) {
                foreach ($queries as $query) {
                    $stmt = $connection->execute($query);
                    $stmt->closeCursor();
                }
            }
        );
    }

    /**
     * TemporaryTable Drop
     *
     * @return void
     * @throws \Exception
     */
    public function dropTempTable()
    {
        $schema = new TableSchema('temp_dates');
        $schema
            ->addColumn('date', ['type' => TableSchema::TYPE_DATE])
            ->setTemporary(true);

        $this->setSchema($schema);

        $connection = $this->getConnection();
        $queries = $schema->dropSql($connection);

        $connection->transactional(
            function (Connection $connection) use ($queries) {
                foreach ($queries as $query) {
                    $stmt = $connection->execute($query);
                    $stmt->closeCursor();
                }
            }
        );
    }
}
