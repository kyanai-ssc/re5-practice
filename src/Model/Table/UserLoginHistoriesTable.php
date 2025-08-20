<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\UserLoginHistory;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * UserLoginHistories Model
 *
 * @method \App\Model\Entity\UserLoginHistory newEmptyEntity()
 * @method \App\Model\Entity\UserLoginHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserLoginHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserLoginHistory get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserLoginHistory findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserLoginHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserLoginHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserLoginHistory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLoginHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLoginHistory[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLoginHistory[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLoginHistory[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLoginHistory[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UserLoginHistoriesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
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
            'id', 'user_id', 'last_login_timestamp', 'error_count', 'last_error_timestamp', 'lock_timestamp',
        ])->where([
            'user_id' => $options['user_id'],
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
            $userId = $loginInfo->getIdentifier();
            $login = true;
        } else {
            $usersTable = $this->getTableLocator()->get('Users');
            $user = $usersTable->find('login')->where(['login_id' => $loginId])->first();

            if (!$user instanceof EntityInterface) {
                return false;
            }

            $userId = $user->get('id');
            $login = false;
        }

        $lockEntity = $this->find('lock', ['user_id' => $userId])->first();

        if (!$lockEntity instanceof UserLoginHistory) {
            $lockEntity = $this->newEntity([
                'user_id',
                'last_login_timestamp',
                'error_count',
                'last_error_timestamp',
                'lock_timestamp',
            ]);
            $lockEntity->set('user_id', $userId);
        }

        $locked = $lockEntity->isLock();
        if ($locked) {
            return false;
        }

        $success = $this->updateOnLogin($lockEntity, $login);
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
     * デフォルトのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefault(Query $query, array $options)
    {
        $query->select([
            'id',
            'user_id',
            'last_login_timestamp',
            'error_count',
            'last_error_timestamp',
            'lock_timestamp',
        ]);

        $userId = Hash::get($options, 'inputs.user_id');
        $query->where([
            'user_id' => $userId,
        ]);

        return $query;
    }

    /**
     * ログイン時のデータ更新
     *
     * @param \Cake\Datasource\EntityInterface $entity 会員
     * @param bool $login ログイン成功可否
     * @return \App\Model\Entity\UserLoginHistory|bool
     */
    public function updateOnLogin(EntityInterface $entity, $login)
    {
        if (!($entity instanceof UserLoginHistory)) {
            throw new CakeException();
        }

        if ($login) {
            $entity->set('last_login_timestamp', clone $this->commonData()->getNowDateTime());
            $entity->unlockAccount();
        } else {
            $entity->addErrorCount();
        }

        return $this->save($entity);
    }

    /**
     * パスワード更新時のデータ更新
     *
     * @param int $userId 会員ID
     * @return void
     */
    public function updateOnPasswordChange(int $userId)
    {
        $entity = $this->find('default', [
            'inputs' => [
                'user_id' => $userId,
            ],
        ])->first();

        if (!($entity instanceof EntityInterface)) {
            return;
        }
        if (!($entity instanceof UserLoginHistory)) {
            throw new CakeException();
        }

        $entity->unlockAccount();
        $this->save($entity);
    }

    /**
     * 初期登録データを生成
     *
     * @return \Cake\Datasource\EntityInterface 初期登録データ
     */
    public function createDefaultData()
    {
        $defaultData = $this->newEntity([
            'last_login_timestamp' => null,
            'error_count' => null,
            'last_error_timestamp' => null,
            'lock_timestamp' => null,
        ], ['validate' => false]);

        return $defaultData;
    }
}
