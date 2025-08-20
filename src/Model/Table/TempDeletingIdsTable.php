<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchema;

/**
 * TempDeletingIds Model
 */
class TempDeletingIdsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $schema = new TableSchema('temp_deleting_ids', [
            'id' => [
                'type' => TableSchema::TYPE_BIGINTEGER,
            ],
        ]);
        $schema->setTemporary(true);
        $this->setSchema($schema);
    }

    /**
     * 一時テーブルを作成
     *
     * @return void
     */
    public function createTempTable()
    {
        $this->getConnection()->transactional(function ($connection) {
            $schema = $this->getSchema();
            if (!($schema instanceof TableSchema)) {
                throw new CakeException();
            }

            foreach ($schema->createSql($connection) as $query) {
                $statement = $connection->execute($query);
                $statement->closeCursor();
            }
        });
    }

    /**
     * 一時テーブルを削除
     *
     * @return void
     */
    public function dropTempTable()
    {
        $this->getConnection()->transactional(function ($connection) {
            $schema = $this->getSchema();
            if (!($schema instanceof TableSchema)) {
                throw new CakeException();
            }

            foreach ($schema->dropSql($connection) as $query) {
                $statement = $connection->execute($query);
                $statement->closeCursor();
            }
        });
    }
}
