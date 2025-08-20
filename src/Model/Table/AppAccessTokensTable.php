<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\AppAccessToken;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * AppAccessTokens Model
 *
 * @method \App\Model\Entity\AppAccessToken newEmptyEntity()
 * @method \App\Model\Entity\AppAccessToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AppAccessToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AppAccessToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\AppAccessToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AppAccessToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AppAccessToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AppAccessToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppAccessToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AppAccessToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppAccessToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppAccessToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AppAccessToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AppAccessTokensTable extends AppTable
{
    public const ACCESS_TOKEN_LENGTH = 16;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }

    /**
     * 有効期限の更新用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUpdate(Query $query, array $options)
    {
        $query->select([
            'id',
            'admin_id',
            'token',
            'expiration_timestamp',
        ]);

        $query->where([
            'admin_id' => $options['admin_id'],
        ]);

        $query->order(['id' => 'ASC']);

        return $query;
    }

    /**
     * エンティティを生成
     *
     * @param string|int $adminId 管理者ID
     * @return array|\Cake\Datasource\EntityInterface
     */
    public function createEntity($adminId)
    {
        $appAccessToken = $this->find('Update', ['admin_id' => $adminId])->first();
        if (empty($appAccessToken)) {
            $appAccessToken = $this->newEntity(['admin_id' => $adminId]);
        }

        return $appAccessToken;
    }

    /**
     * アクセストークンを生成
     *
     * @param string|int $adminId 管理者のID
     * @return string
     */
    public function createAccessToken($adminId)
    {
        return Security::hash(((string)$adminId) . Security::randomString(static::ACCESS_TOKEN_LENGTH), 'sha256');
    }

    /**
     * 有効期限を取得
     *
     * @return \Cake\I18n\FrozenTime
     */
    public function getExpirationTime()
    {
        $now = $this->commonData()->getNowDateTime();
        $expiration = Configure::readOrFail('Master.api.AppAccessToken.expiration');
        $expirationTime = $now->addSeconds($expiration);

        return $expirationTime;
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!($entity instanceof AppAccessToken)) {
            throw new CakeException();
        }

        if ($entity->isNew()) {
            // アクセストークンを生成
            $entity->set('token', $this->createAccessToken($entity->get('admin_id')));
        }

        // 有効期限を生成
        $entity->set('expiration_timestamp', $this->getExpirationTime());
    }

    /**
     * リクエストされたトークンと同じIDを取得
     *
     * @return \App\Model\Entity\AppAccessToken|null
     * @param string|null $postToken リクエストされたトークン
     */
    public function getAppAccessToken($postToken)
    {
        $query = $this->find()->select([
            'id',
            'admin_id',
            'token',
            'expiration_timestamp',
        ]);
        $query->contain([
            'Admins' => [
                'fields' => [
                    'id',
                    'authority',
                    'login_id',
                    'label_id',
                ],
            ],
            'Admins.AdminAuthorities' => [
                'fields' => [
                    'access_setting',
                    'access_operator',
                ],
            ],
        ]);
        $query->join([
            'Admins' => [
                'table' => 'admins',
                'type' => 'LEFT',
                'conditions' => 'admins.id = AppAccessTokens.admin_id',
            ],
        ]);
        $query->where([
            'AppAccessTokens.token' => $postToken,
        ]);

        $entity = $query->first();
        if (!($entity instanceof AppAccessToken)) {
            return null;
        }

        return $entity;
    }
}
