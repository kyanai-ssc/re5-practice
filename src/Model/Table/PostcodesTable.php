<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;

/**
 * Postcodes Model
 *
 * @method \App\Model\Entity\Postcode newEmptyEntity()
 * @method \App\Model\Entity\Postcode newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Postcode[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Postcode get($primaryKey, $options = [])
 * @method \App\Model\Entity\Postcode findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Postcode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Postcode[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Postcode|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Postcode saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Postcode[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Postcode[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Postcode[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Postcode[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PostcodesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * 住所検索のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchAddress(Query $query, array $options)
    {
        $query->select([
            'state',
            'state_kana',
            'state_id',
            'city',
            'city_kana',
            'address',
            'address_kana',
            'company',
            'company_kana',
        ]);
        $query->where([
            'Postcodes.zip_code' => $options['inputs']['zip_code'],
        ]);
        $query->order([
            'Postcodes.id' => 'ASC',
        ]);

        $query->enableHydration(false);
        $query->formatResults(function ($postcodes) {
            $result = $postcodes->map(function ($postcode) {
                return array_map('strval', $postcode);
            });

            return $result;
        });

        return $query;
    }
}
