<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * NewsAuthorities Model
 *
 * @method \App\Model\Entity\NewsAuthority newEmptyEntity()
 * @method \App\Model\Entity\NewsAuthority newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\NewsAuthority[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\NewsAuthority get($primaryKey, $options = [])
 * @method \App\Model\Entity\NewsAuthority findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\NewsAuthority patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\NewsAuthority[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\NewsAuthority|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\NewsAuthority saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\NewsAuthority[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\NewsAuthority[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\NewsAuthority[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\NewsAuthority[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class NewsAuthoritiesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('News', [
            'foreignKey' => 'news_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('UserAuthorities', [
            'foreignKey' => 'user_authority_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $userAuthorityLists = $userAuthoritiesTable->getSelectList();

        $fieldValueOptions = [
            'userAuthorityId' => $userAuthorityLists,
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('user_authority_id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('user_authority_id')
            ->add('user_authority_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('userAuthorityId'))],
                    'last' => true,
                    'on' => function ($context) {
                        if (empty(Hash::get($context['data'], 'user_authority_id'))) {
                            return false;
                        }

                        return true;
                    },
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }
}
