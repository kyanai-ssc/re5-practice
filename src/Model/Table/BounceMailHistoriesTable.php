<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * BounceMailHistories Model
 *
 * @method \App\Model\Entity\BounceMailHistory newEmptyEntity()
 * @method \App\Model\Entity\BounceMailHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\BounceMailHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BounceMailHistory get($primaryKey, $options = [])
 * @method \App\Model\Entity\BounceMailHistory findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\BounceMailHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BounceMailHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BounceMailHistory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BounceMailHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BounceMailHistory[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMailHistory[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMailHistory[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMailHistory[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class BounceMailHistoriesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('BounceMails', [
            'foreignKey' => 'bounce_mail_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('bounce_mail_id');
    }

    /**
     * ダウンロードファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDownload(Query $query, array $options = [])
    {
        $query->select(['id', 'bounce_mail_id', 'contents']);

        return $query;
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options = [])
    {
        $query->select(['id', 'bounce_mail_id', 'user_id', 'created']);
        $query->order(['BounceMailHistories.created' => 'DESC']);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }
}
