<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\AdminPassResetToken;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * AdminPassResetTokens Model
 *
 * @method \App\Model\Entity\AdminPassResetToken newEmptyEntity()
 * @method \App\Model\Entity\AdminPassResetToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminPassResetToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminPassResetToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminPassResetToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminPassResetTokensTable extends AppTable
{
    use MailerAwareTrait;

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

        $this->addBehavior('Token', [
            'expirationAddHour' => AdminPassResetToken::EXPIRATION_ADD_HOUR,
            'adminFlg' => true,
        ]);
    }

    /**
     * afterSave hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $this->getMailer('Admin')->send('adminPassReset', [$entity->get('mail'), $entity]);

        return true;
    }

    /**
     * トークン生成用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAdmin(Query $query, array $options)
    {
        $adminId = Hash::get($options, 'adminId');
        $query->select(['id', 'admin_id'])->where(['admin_id' => $adminId]);

        return $query;
    }

    /**
     * トークン取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findToken(Query $query, array $options)
    {
        $token = Hash::get($options, 'token', '');
        $query->select(['token', 'admin_id'])->contain([
            'Admins' => [
                'fields' => [
                    'id',
                    'initial_password',
                ],
            ],
        ]);

        $query->where(['token' => $token]);
        $query->where(['expiration_timestamp >=' => $this->commonData()->getNowDateTime()]);

        return $query;
    }

    /**
     * パスワードリセット
     *
     * @param string $token token
     * @throws \Exception
     * @return bool
     */
    public function resetPassword($token)
    {
        $tokenData = $this->find('token', ['token' => $token])->first();

        if (empty($tokenData) || !$tokenData instanceof EntityInterface) {
            return false;
        }

        $adminData = $tokenData->get('admin');
        $result = $this->getConnection()->transactional(function () use ($adminData) {
            /** @var \App\Model\Table\AdminsTable $adminsTable */
            $adminsTable = $this->getTableLocator()->get('Admins');

            if (!$adminsTable->resetPassword($adminData->get('id'), $adminData->get('initial_password'))) {
                return false;
            }

            //既に発行済みの同管理者IDのトークンを削除
            $result = $this->deleteAll(['admin_id' => $adminData->get('id')]);

            return $result;
        });

        return $result;
    }
}
