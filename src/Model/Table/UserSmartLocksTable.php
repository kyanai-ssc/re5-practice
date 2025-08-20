<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * UserSmartLocks Model
 *
 * @method \App\Model\Entity\UserSmartLock newEmptyEntity()
 * @method \App\Model\Entity\UserSmartLock newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserSmartLock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserSmartLock get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserSmartLock findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserSmartLock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserSmartLock[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserSmartLock|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserSmartLock saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserSmartLock[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSmartLock[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSmartLock[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserSmartLock[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UserSmartLocksTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasOne('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * 会員登録時の自動返信メール用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUserAddAutoReplyMail(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'smart_lock_user_id',
        ]);

        $userId = Hash::get($options, 'user_id');
        if (isset($userId)) {
            $query->where(['user_id' => $userId]);
        }

        return $query;
    }
}
