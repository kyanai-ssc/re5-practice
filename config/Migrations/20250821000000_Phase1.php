<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Phase1 class.
 *
 * @psalm-suppress UnusedClass
 */
class Phase1 extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up()
    {
        $this->table('label_authorities', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'biginteger', [
                'autoIncrement' => true,
                'generated' => null,
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('label_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('user_authority_id', 'biginteger', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'label_id',
                    'user_authority_id',
                ],
                ['unique' => true, 'name' => 'label_authorities_label_id_user_authority_id']
            )
            ->addIndex(
                [
                    'user_authority_id',
                ],
                ['name' => 'label_authorities_user_authority_id']
            )
            ->create();

        $this->table('user_authorities')
            ->addColumn('charge_multiplier', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->update();
        
        $this->table('options')
            ->addColumn('option_unit_time', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down()
    {
        // $this->table('label_authorities')->drop()->save();

        // $this->table('user_authorities')
        //     ->removeColumn('charge_multiplier')
        //     ->update();

        // $this->table('options')
        //     ->removeColumn('option_unit_time')
        //     ->update();
    }
}
