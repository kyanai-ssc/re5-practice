<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\UserPasswordReminderToken;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * UserPasswordReminderTokens Model
 *
 * @method \App\Model\Entity\UserPasswordReminderToken newEmptyEntity()
 * @method \App\Model\Entity\UserPasswordReminderToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserPasswordReminderToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @method \Cake\Validation\Validator tokenMailValidator(\Cake\Validation\Validator $validator)
 */
class UserPasswordReminderTokensTable extends AppTable
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

        $this->addBehavior('Token', [
            'expirationAddHour' => UserPasswordReminderToken::EXPIRATION_ADD_HOUR,
            'userFlg' => true,
        ]);
    }

    /**
     * トークンチェック用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUser(Query $query, array $options)
    {
        $userId = Hash::get($options, 'userId');
        $query->select(['id', 'user_id'])->where(['user_id' => $userId]);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $this->tokenMailValidator($validator);
    }

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

        $user = Hash::get($options, 'user');
        $autoReplyMailHistoriesTable->sendPasswordReminder($user, $entity);
    }
}
