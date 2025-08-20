<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\AdminLoginHistory;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;

/**
 * AdminLoginHistories Model
 *
 * @method \App\Model\Entity\AdminLoginHistory newEmptyEntity()
 * @method \App\Model\Entity\AdminLoginHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminLoginHistory get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminLoginHistory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminLoginHistory[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminLoginHistoriesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
    {
        if (!($entity instanceof AdminLoginHistory)) {
            throw new CakeException();
        }

        $now = $this->commonData()->getNowDateTime();

        if ($options['login']) {
            if (empty($options['release'])) {
                $entity->set('last_login_timestamp', $now);
            }
            $entity->unlockAccount();
        } else {
            $entity->addErrorCount();
        }
    }

    /**
     * ロック情報取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findLock(Query $query, array $options)
    {
        $query->select([
            'id', 'admin_id', 'last_login_timestamp', 'error_count', 'last_error_timestamp', 'lock_timestamp',
        ])->where([
            'admin_id' => $options['admin_id'],
        ]);

        return $query;
    }

    /**
     * ログイン履歴を登録
     *
     * @param \Authentication\IdentityInterface|null $loginInfo ログイン情報
     * @param string $loginId ログインID
     * @return bool
     */
    public function saveLoginLogs($loginInfo, $loginId)
    {
        if (isset($loginInfo)) {
            $adminId = $loginInfo->getIdentifier();
            $login = true;
        } else {
            $adminsTable = $this->getTableLocator()->get('Admins');
            $admin = $adminsTable->find('login')->where(['login_id' => $loginId])->first();

            if (!$admin instanceof EntityInterface) {
                return false;
            }

            $adminId = $admin->get('id');
            $login = false;
        }

        $lockEntity = $this->find('lock', ['admin_id' => $adminId])->first();

        if (!$lockEntity instanceof AdminLoginHistory) {
            $lockEntity = $this->newEmptyEntity();
            $lockEntity->set('admin_id', $adminId);
        }
        if (!($lockEntity instanceof AdminLoginHistory)) {
            throw new CakeException();
        }

        $locked = $lockEntity->isLock();
        if ($locked) {
            return false;
        }

        $success = $this->save($lockEntity, ['validation' => false, 'login' => $login]);
        $locked = $lockEntity->isLock();
        if ($locked) {
            return false;
        }

        if (isset($loginInfo) && $success) {
            return true;
        }

        return false;
    }

    /**
     * アカウントロック情報を開放
     *
     * @param int|null $adminId 管理者ID
     * @return void
     */
    public function releaseAccountLock($adminId)
    {
        if (!isset($adminId)) {
            return;
        }

        $lock = $this->find('lock', ['admin_id' => $adminId])->first();
        if ($lock instanceof EntityInterface) {
            $lockEntity = $this->patchEntity($lock, ['admin_id' => $adminId]);
        } else {
            return;
        }

        $this->save($lockEntity, ['validation' => false, 'login' => true, 'release' => true]);
    }
}
