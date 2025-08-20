<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\OptinToken;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * OptinTokens Model
 *
 * @method \App\Model\Entity\OptinToken newEmptyEntity()
 * @method \App\Model\Entity\OptinToken newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\OptinToken[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OptinToken get($primaryKey, $options = [])
 * @method \App\Model\Entity\OptinToken findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\OptinToken patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OptinToken[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OptinToken|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OptinToken saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OptinToken[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptinToken[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptinToken[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptinToken[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 * @method \Cake\Validation\Validator tokenMailValidator(\Cake\Validation\Validator $validator)
 */
class OptinTokensTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('Token', [
            'expirationAddHour' => OptinToken::EXPIRATION_ADD_HOUR,
            'mailConfirm' => true,
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
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // メールアドレス重複チェック
        $rules->add(
            function ($entity) {
                /** @var \App\Model\Table\UsersTable $usersTable */
                $usersTable = $this->getTableLocator()->get('Users');

                if ($entity->hasErrors()) {
                    return true;
                }

                $query = $usersTable->find('mailIsUnique', [
                    'inputs' => [
                        'mail' => $entity->get('mail'),
                    ],
                ]);
                if ($query->count() > 0) {
                    return false;
                }

                return true;
            },
            'mailIsUnique',
            [
                'errorField' => 'mail',
                'message' => __(Message::ERROR_EXISTS),
            ]
        );

        return $rules;
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

        $urlParameter = Hash::get($options, 'optinUrlParameter');
        $autoReplyMailHistoriesTable->sendOptin($entity, $urlParameter);
    }
}
