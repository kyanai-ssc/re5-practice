<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * LabelAuthorities Model
 *
 * @method \App\Model\Entity\LabelAuthority newEmptyEntity()
 * @method \App\Model\Entity\LabelAuthority newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\LabelAuthority[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\LabelAuthority get($primaryKey, $options = [])
 * @method \App\Model\Entity\LabelAuthority findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\LabelAuthority patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\LabelAuthority[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\LabelAuthority|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LabelAuthority saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\LabelAuthority[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LabelAuthority[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\LabelAuthority[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\LabelAuthority[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class LabelAuthoritiesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
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

        $userAuthorityLists = $userAuthoritiesTable->getSelectList(false);

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
