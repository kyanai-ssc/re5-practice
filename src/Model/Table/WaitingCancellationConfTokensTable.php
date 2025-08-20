<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\WaitingCancellationConfToken;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;

/**
 * WaitingCancellationConfTokens Model
 *
 * @method \App\Model\Entity\WaitingCancellationConfToken newEmptyEntity()
 * @method \App\Model\Entity\WaitingCancellationConfToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\WaitingCancellationConfToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @method \Cake\Validation\Validator tokenMailValidator(\Cake\Validation\Validator $validator)
 */
class WaitingCancellationConfTokensTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('Token', [
            'expirationAddHour' => WaitingCancellationConfToken::EXPIRATION_ADD_HOUR,
        ]);
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

        $autoReplyMailHistoriesTable->sendWaitingCancellationToken($entity);
    }
}
