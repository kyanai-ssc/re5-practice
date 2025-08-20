<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Tag;
use App\Model\Entity\TagGroup;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * TagGroups Model
 *
 * @method \App\Model\Entity\TagGroup newEmptyEntity()
 * @method \App\Model\Entity\TagGroup newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TagGroup[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TagGroup get($primaryKey, $options = [])
 * @method \App\Model\Entity\TagGroup findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\TagGroup patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TagGroup[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TagGroup|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TagGroup saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TagGroup[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TagGroup[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\TagGroup[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TagGroup[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TagGroupsTable extends AppTable
{
    public const TAG_GROUP_NAME_MAX = 100;
    public const TAG_GROUP_SORT_NO_MAX = 10000000000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Tags', [
            'foreignKey' => 'tag_group_id',
            'sort' => ['Tags.sort_no' => 'ASC', 'Tags.id' => 'ASC'],
            'saveStrategy' => 'replace',
            'cascadeCallbacks' => true,
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'publicFlg' => Configure::readOrFail('Master.tag.publicFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        if (!isset($data['tags']) || !is_array($data['tags'])) {
            $data->offsetSet('tags', []);
        }
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::TAG_GROUP_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TAG_GROUP_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('sort_no', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('sort_no', __(Message::ERROR_NOT_EMPTY), false)
            ->add('sort_no', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::TAG_GROUP_SORT_NO_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::TAG_GROUP_SORT_NO_MAX),
                ],
            ]);

        $validator
            ->requirePresence('public_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('public_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('public_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('publicFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('tags', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyArray('tags', __(Message::ERROR_NOT_EMPTY), false)
            ->array('tags', __(Message::ERROR_NOT_EMPTY));

        return $validator;
    }

    /**
     * タググループごとのタグ情報を取得
     *
     * @param bool $publicFlg 公開フラグ
     * @return array
     */
    public function getTagsList($publicFlg = false)
    {
        $query = $this->find('all');
        $query->select(['id', 'name']);
        $query->contain([
            'Tags' => [
                'fields' => ['id', 'sort_no', 'tag_group_id', 'name'],
            ],
        ]);

        if ($publicFlg) {
            $query->where(['TagGroups.public_flg' => TagGroup::PUBLIC_FLG_ON]);
            $query->contain([
                'Tags' => function (Query $q) {
                    $q->select(['id', 'sort_no', 'tag_group_id', 'name']);
                    $q->where(['Tags.public_flg' => Tag::PUBLIC_FLG_ON]);

                    return $q;
                },
            ]);
        }

        $query->orderAsc('TagGroups.sort_no')
            ->orderAsc('TagGroups.id');

        $tagList = [];
        foreach ($query as $row) {
            $tagList[$row['id']]['name'] = $row['name'];
            $tagList[$row['id']]['tag'] = Hash::combine($row['tags'], '{n}.id', '{n}.name');
        }

        return $tagList;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->value('public_flg', [
                'multiValue' => true,
            ]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query
            ->order([
                    'TagGroups.' . $sort => $direction,
                ] + [
                    'TagGroups.sort_no' => $direction,
                    'TagGroups.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 編集のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'sort_no',
            'public_flg',
        ])->contain([
            'Tags' => [
                'fields' => ['id', 'tag_group_id', 'name', 'sort_no', 'public_flg'],
            ],
        ]);

        return $query;
    }

    /**
     * 削除のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select([
            'id',
        ])->contain([
            'Tags' => [
                'fields' => ['id', 'tag_group_id'],
            ],
        ]);

        return $query;
    }
}
