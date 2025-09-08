<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Label;
use App\Utility\ArrayUtility;
use App\Validation\CustomValidation;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * Labels Model
 *
 * @method \App\Model\Entity\Label newEmptyEntity()
 * @method \App\Model\Entity\Label newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Label[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Label get($primaryKey, $options = [])
 * @method \App\Model\Entity\Label findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Label patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Label[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Label|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Label saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Label[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class LabelsTable extends AppTable
{
    public const LABEL_NAME_MAX = 100;
    public const LABEL_SORT_NO_MAX = 10000000000;

    public const TYPE_CREATE = 'create';
    public const TYPE_OTHER = 'other';
    public const TYPE_SELF = 'self';
    public const TYPE_RESERVATIONS = 'reservations';

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('ParentLabels', [
            'className' => 'Labels',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('Admins', [
            'foreignKey' => 'label_id',
        ]);
        $this->hasMany('AutoReplyMails', [
            'foreignKey' => 'label_id',
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'label_id',
        ]);
        $this->hasMany('ChildLabels', [
            'className' => 'Labels',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('News', [
            'foreignKey' => 'label_id',
        ]);

        $this->hasMany('LabelAuthorities', [
            'foreignKey' => 'label_id',
            'saveStrategy' => 'replace',
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, \ArrayObject $options)
    {
        $labelAuthorities = $entity->get('label_authorities');

        $setLabelAuthorities = [];
        foreach ($labelAuthorities as $labelAuthority) {
            if (!empty($labelAuthority->get('user_authority_id'))) {
                $setLabelAuthorities[] = $labelAuthority;
            }
        }

        $entity->set('label_authorities', $setLabelAuthorities);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\LabelAuthoritiesTable $labelAuthoritiesTable */
        $labelAuthoritiesTable = $this->fetchTable('LabelAuthorities');

        $fieldValueOptions = [
            'publicFlg' => Configure::readOrFail('Master.label.publicFlg'),
            'userAuthorityId' => $labelAuthoritiesTable->getFieldValueOptions('userAuthorityId'),
        ];

        return $fieldValueOptions;
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
                    'rule' => ['maxLength', static::LABEL_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::LABEL_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('parent_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('parent_id')
            ->add('parent_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'exists' => [
                    'rule' => [
                        'callback' => [$this, 'checkSelectLimit'],
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'depth' => [
                    'rule' => function ($check) {
                        return $this->exists([
                            'id' => $check,
                        ]);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
                'depthEdit' => [
                    'rule' => function ($check, $context) {
                        $parentArr = Hash::combine($this->find('parents', [
                            'inputs' => [
                                'id' => $check,
                            ],
                        ])->toArray(), '{n}.parent_id');
                        $childrenArr = Hash::combine(
                            $this->getChildrenDataById($context['data']['id']),
                            '{n}.parent_id'
                        );
                        $depth = count($parentArr) + count($childrenArr) - 1;

                        if ($depth >= Configure::readOrFail('Setting.label.depth')) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_LABEL_LEVEL),
                    'on' => function ($context) {
                        if (isset($context['data']['id'])) {
                            return true;
                        }

                        return false;
                    },

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
            ->requirePresence('sort_no', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('sort_no')
            ->add('sort_no', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::LABEL_SORT_NO_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::LABEL_SORT_NO_MAX),
                ],
            ]);

        $validator
            ->requirePresence('label_authorities', false)
            ->allowEmptyArray('label_authorities')
            ->array('label_authorities', __(Message::ERROR_INVALID_VALUE));

        return $validator;
    }

    /**
     * ラベル検証用バリデータ
     *
     * @param \Cake\Validation\Validator $validator validator
     * @param string $fieldName fieldName default 'label_id'
     * @param int|null $parentLabelId 上位ラベルID
     * @return \Cake\Validation\Validator
     */
    public function addValidateLabelId(Validator $validator, $fieldName = 'label_id', ?int $parentLabelId = null)
    {
        $validator->add($fieldName, [
            'isScalar' => [
                'rule' => ['isScalar'],
                'last' => true,
                'message' => __(Message::ERROR_INVALID_VALUE),
            ],
            'integer' => [
                'rule' => ['integer', CustomValidation::BIGINT_MAX],
                'last' => true,
                'message' => __(Message::ERROR_IN_LIST),
            ],
            'exists' => [
                'rule' => function ($check) {
                    return $this->exists([
                        'id' => $check,
                    ]);
                },
                'last' => true,
                'message' => __(Message::ERROR_IN_LIST),
            ],
            'parent' => [
                'rule' => function ($check) use ($parentLabelId) {
                    $labelList = $this->find('parents', [
                        'inputs' => [
                            'id' => $check,
                        ],
                    ])->first();

                    $valid = false;
                    if (is_array($labelList)) {
                        foreach ($labelList as $label) {
                            if (isset($label['id']) && ((string)$label['id']) === ((string)$parentLabelId)) {
                                $valid = true;
                                break;
                            }
                        }
                    }

                    return $valid;
                },
                'last' => true,
                'message' => __(Message::ERROR_IN_LIST),
                'on' => function () use ($parentLabelId) {
                    if (!isset($parentLabelId)) {
                        return false;
                    }

                    return true;
                },
            ],
        ]);

        return $validator;
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
        $query->select(
            ['id', 'name', 'public_flg', 'parent_id', 'sort_no']
        );
        $query->contain([
            'LabelAuthorities' => [
                'fields' => [
                    'id',
                    'label_id',
                    'user_authority_id',
                ],
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
        $query->select(
            ['id', 'parent_id']
        )->contain([
            'ChildLabels' => ['fields' => ['id', 'parent_id']],
            'Admins' => ['fields' => ['id', 'label_id']],
            'Events' => ['fields' => ['id', 'label_id']],
            'AutoReplyMails' => ['fields' => ['id', 'label_id']],
            'News' => ['fields' => ['id', 'label_id']],
        ]);

        return $query;
    }

    /**
     * 親データ取得時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findParents(Query $query, array $options)
    {
        $columns = [
            'id',
            'parent_id',
            'name',
            'public_flg',
        ];
        $this->joinQuery($query, 'Labels', $columns);

        $id = Hash::get($options, 'inputs.id');
        if (isset($id)) {
            $query->where([
                'Labels.id' => $id,
            ]);
        } else {
            $query->where([
                'Labels.id IS NULL',
            ]);
        }

        $query->enableHydration(false);

        $query->formatResults(function ($labels) use ($columns) {
            $result = $labels->map(function ($label) use ($columns) {
                return $this->splitLabelData($label, $columns);
            });

            return $result;
        });

        return $query;
    }

    /**
     * parent_idのコールバック関数
     *
     * @param int|string $value 値
     * @param array $context context
     * @return bool
     */
    public function checkSelectLimit($value, $context)
    {
        $label = $this->find('parents', [
            'inputs' => [
                'id' => $value,
            ],
        ]);
        if ($label->count() >= Configure::readOrFail('Setting.label.depth')) {
            return false;
        }

        return true;
    }

    /**
     * 指定されたIDから下位ラベルの情報を取得する
     *
     * @param int|array $id ID
     * @param bool $publicFlg 公開フラグ
     * @return array
     */
    protected function getChildrenDataById($id, $publicFlg = false)
    {
        $result = [];

        $ids = $id;
        if (!is_array($ids)) {
            $parentData = $this->getChildDataByParentId($ids, $publicFlg);
            foreach ($parentData as $labelId => $data) {
                $result[$labelId] = $data;
            }
            $ids = array_keys($result);
        }

        while ($ids) {
            $childrenData = $this->getChildrenDataByParentsId($ids);

            $ids = [];
            foreach ($childrenData as $childId => $data) {
                $result[$childId] = $data;
                $ids[] = $childId;
            }
        }

        return $result;
    }

    /**
     * 上位ラベルから下位ラベルを取得する
     *
     * @param array $ids ID
     * @return array
     */
    protected function getChildrenDataByParentsId($ids)
    {
        $select = $this->find()->select();
        $query = $select
            ->where([
                'parent_id IN ' => $ids,
            ])
            ->order(['sort_no' => 'ASC', 'id' => 'ASC']);

        $result = [];

        foreach ($query as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
    }

    /**
     * 上位ラベルから下位ラベルを取得する
     *
     * @param int|null $parentId 上位ID
     * @param bool $onlyPublic 公開フラグ
     * @return array
     */
    protected function getChildDataByParentId($parentId, $onlyPublic = false)
    {
        $select = $this->find()->select();

        if (((string)$parentId) !== '') {
            $select->where(['labels.parent_id' => $parentId]);
        } else {
            $select->where(['labels.parent_id IS' => null]);
        }

        if ($onlyPublic) {
            $select->where([
                'labels.public_flg' => Configure::readOrFail('Master.common.flg.on'),
            ]);
        }

        $query = $select->order(['sort_no' => 'ASC', 'id' => 'ASC']);
        $result = [];

        foreach ($query as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
    }

    /**
     * 指定された上位ラベルIDから情報を取得する
     *
     * @param int|null $parentId 上位ID
     * @param int|null $excludeId 除外ID
     * @param bool $onlyPublic 公開フラグ
     * @return array
     */
    protected function getNameDataByParentId($parentId = null, $excludeId = null, $onlyPublic = false)
    {
        $where = [];

        if ($parentId === null) {
            $where[] = ['Labels.parent_id IS' => null];
        } else {
            $where[] = ['Labels.parent_id' => $parentId];
        }

        if ($onlyPublic) {
            $where += ['public_flg' => Configure::readOrFail('Master.common.flg.on')];
        }

        if (!is_null($excludeId) && $excludeId != '') {
            $where += ['id != ' => $excludeId];
        }

        $query = $this->find()->select(['id', 'parent_id', 'name']);
        $list = $query->where($where)
            ->order(['sort_no' => 'ASC', 'id' => 'ASC']);

        $result = [];
        foreach ($list as $data) {
            $result[$data->id] = $data->name;
        }

        return $result;
    }

    /**
     * 該当が親ラベルの情報を取得し返却
     *
     * @param int|null $parentId 上位ID
     * @param int|null $excludeId 除外ID
     * @param bool $onlyPublic 公開フラグ
     * @param int|null $displayMax 階層制限
     * @param int|null $fixationId 該当ラベルに固定
     * @return array
     */
    public function getParentLabel(
        $parentId = null,
        $excludeId = null,
        $onlyPublic = false,
        $displayMax = null,
        $fixationId = null
    ) {
        $labelIds = [];
        if (!isset($displayMax)) {
            $displayMax = Configure::readOrFail('Setting.label.depth');
        }

        $isAdmin = $this->commonData()->existsAdminLoginData();

        if ($isAdmin) {
            $adminLabelId = $this->commonData()->getAdminLoginLabel();
            // 管理者の担当ラベル（またはその下位ラベル）以外が指定されていた場合は、強制的に管理者の担当ラベルを指定する
            if (!$this->isAdminUsableLabel((int)$parentId)) {
                $parentId = $adminLabelId;
            }
        }

        // 指定のparentIdの親ラベル情報を取得
        $parentsData[]['id'] = null;
        $fixEndFlg = false;
        $fixationIdDepth = 0;
        foreach ($this->find('parents', ['inputs' => ['id' => $parentId]])->toArray() as $parents) {
            foreach ($parents as $parent) {
                $parentsData[] = $parent;
                if (!is_null($fixationId) && !$fixEndFlg) {
                    $fixationIdDepth++;
                }

                if (!is_null($fixationId) && (string)$parent['id'] === (string)$fixationId) {
                    $fixEndFlg = true;
                }
            }
        }

        if (!$isAdmin) {
            $labelIds = $this->getLabelIdByUserAuthority();
        }

        $depth = 1;
        $value = [];
        $data = [];
        foreach ($parentsData as $parent) {
            if ($parent['id'] !== null) {
                $value[] = $parent['id'];
            }

            $tmp = $this->getNameDataByParentId($parent['id'], $excludeId, $onlyPublic);
            if ($this->commonData()->existsUserLoginData()) {
                // $tmpに入っているラベルidと上記で取得したlabel_idが一致するデータのみを取得し上書き
                $tmp = array_intersect_key($tmp, array_flip($labelIds));
            }

            if ($tmp && ($depth <= $displayMax)) {
                if (is_null($fixationId)) {
                    $data[] = $tmp;
                } else {
                    if ($depth > $fixationIdDepth) {
                        $data[] = $tmp;
                    } else {
                        $data[][$parentsData[$depth]['id']] = $tmp[$parentsData[$depth]['id']];
                    }
                }
            }
            $depth++;
        }

        if ($isAdmin) {
            $adminUsableLabelIds = $this->getAdminUsableLabelIds(true);
            if (!empty($adminUsableLabelIds)) {
                // 管理者の担当カテゴリに紐づかないカテゴリーは削除する
                foreach ($data as $hierarchyKey => $labelsByHierarchy) {
                    foreach (array_keys($labelsByHierarchy) as $labelId) {
                        if (!isset($adminUsableLabelIds[$labelId])) {
                            unset($data[$hierarchyKey][$labelId]);
                        }
                    }
                }
            }
        }

        return [
            'selected' => array_filter($value, function ($id) {
                if (strlen((string)$id) === 0) {
                    return false;
                }

                return true;
            }),
            'list' => $data,
        ];
    }

    /**
     * ラベル検索、登録時のフォーム設定
     *
     * @param string|null $type タイプ
     * @return array
     */
    public function setAjaxForm($type)
    {
        $form = [];

        switch ($type) {
            case static::TYPE_RESERVATIONS:
                $form['search_id'] = 'events_label_id';
                $form['max_depth'] = Configure::read('Setting.label.depth');
                $form['type'] = static::TYPE_RESERVATIONS;
                break;
            case static::TYPE_OTHER:
                $form['search_id'] = 'label_id';
                $form['max_depth'] = Configure::read('Setting.label.depth');
                $form['type'] = static::TYPE_OTHER;
                break;
            case static::TYPE_CREATE:
                $form['search_id'] = 'parent_id';
                $form['max_depth'] = Configure::read('Setting.label.depth') - 1;
                $form['type'] = static::TYPE_CREATE;
                break;
            case static::TYPE_SELF:
                $form['search_id'] = 'parent_id';
                $form['max_depth'] = Configure::read('Setting.label.depth');
                $form['type'] = static::TYPE_SELF;
                break;
            default:
                $form['search_id'] = 'parent_id';
                $form['max_depth'] = Configure::read('Setting.label.depth') - 1;
                $form['type'] = static::TYPE_CREATE;
                break;
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('public_flg', [
                'multiValue' => true,
            ])
            ->like('name', [
                'before' => true,
                'after' => true,
            ])
            ->callback('label_id', [
                'callback' => function ($query, $args, $filter) {
                },
            ])
            ->callback('display_level', [
                'callback' => function ($query, $args, $filter) {
                    $displayLevel = Hash::get($args, 'display_level');
                    if ((string)$displayLevel === (string)Label::DISPLAY_LEVEL_ALL && Hash::get($args, 'label_id')) {
                        $parentOr[] = ['labels.parent_id' => Hash::get($args, 'label_id')];

                        $limit = Configure::readOrFail('Setting.label.depth') - 1;
                        for ($i = 1; $i < $limit; ++$i) {
                            $parentOr += ["labels_{$i}.parent_id" => Hash::get($args, 'label_id')];
                        }

                        $query->where(['OR' => $parentOr]);
                    } elseif ((string)$displayLevel === (string)Label::DISPLAY_LEVEL_ONE) {
                        $labelId = Hash::get($args, 'label_id');
                        if (isset($labelId)) {
                            $query->where(['Labels.parent_id' => $labelId]);
                        } else {
                            $query->where(['Labels.parent_id IS NULL']);
                        }
                    }
                },
            ])
            ->callback('user_authority_id', [
                'callback' => function ($query, $args) {
                    $orWhere = [];
                    $userAuthorities = Hash::get($args, 'user_authority_id');

                    if (!empty($userAuthorities)) {
                        /** @var \App\Model\Table\LabelAuthoritiesTable $labelAuthoritiesTable */
                        $labelAuthoritiesTable = $this->getTableLocator()->get('LabelAuthorities');

                        $labelAuthoritiesQuery = $labelAuthoritiesTable->find();
                        $labelAuthoritiesQuery->select(['label_id']);
                        $labelAuthoritiesQuery->where([
                            'LabelAuthorities.user_authority_id IN' => $userAuthorities,
                        ]);
                        $orWhere[] = [
                            'Labels.id IN' => $labelAuthoritiesQuery,
                        ];
                    }

                    if (!empty($orWhere)) {
                        $query->where([
                            'OR' => $orWhere,
                        ]);
                    }
                },
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
        $query = $this->joinQuery(
            $query,
            'Labels',
            ['id', 'parent_id', 'name', 'sort_no', 'public_flg']
        )->select(['id']);

        $query->contain([
            'ChildLabels' => ['fields' => ['id', 'parent_id']],
            'Admins' => ['fields' => ['id', 'label_id']],
            'Events' => ['fields' => ['id', 'label_id']],
            'AutoReplyMails' => ['fields' => ['id', 'label_id']],
            'News' => ['fields' => ['id', 'label_id']],
            'LabelAuthorities' => ['fields' => ['id', 'label_id', 'user_authority_id']],
            'LabelAuthorities.UserAuthorities' => ['fields' => ['name']],
        ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query
            ->order([
                    'Labels.' . $sort => $direction,
                ] + [
                    'Labels.sort_no' => $direction,
                    'Labels.id' => $direction,
                ], true);

        $query->formatResults(function (CollectionInterface $results) {
            return $results->map(function ($row) {
                $labelData = $this->splitLabelData(
                    $row->toArray(),
                    ['id', 'parent_id', 'name', 'sort_no', 'public_flg']
                );
                $childLabelData = array_pop($labelData);
                $row['data'] = $childLabelData + ['parent' => $labelData];

                return $row;
            });
        });

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 各階層を結合したQueryを取得
     *
     * @param \Cake\ORM\Query $query Query
     * @param string $parentTable 親テーブル
     * @param array $columns カラム
     * @param string|null $tableSuffix サフィックス
     * @param bool $onlyPublic 公開フラグ
     * @return \Cake\ORM\Query
     */
    public function joinQuery(
        Query $query,
        string $parentTable,
        array $columns = [],
        ?string $tableSuffix = null,
        bool $onlyPublic = false
    ) {
        if (count($columns) > 0) {
            $parentColumns = [];
            foreach ($columns as $column) {
                $parentColumns[$column . '_0'] = $parentTable . '.' . $column;
            }
            $query->select($parentColumns);
        }

        $tablePrefix = 'labels';
        if (((string)$tableSuffix) !== '') {
            $tablePrefix .= '_' . $tableSuffix;
        }

        $limit = Configure::readOrFail('Setting.label.depth') - 1;
        for ($i = 0; $i < $limit; ++$i) {
            $tableIndex = $i;
            $tableName = $tablePrefix . '_' . $tableIndex;
            if ($i === 0) {
                $tableName = $parentTable;
            }
            $joinIndex = $i + 1;
            $joinName = $tablePrefix . '_' . $joinIndex;

            $conditions = [
                $tableName . '.parent_id = ' . $joinName . '.id',
            ];
            if ($onlyPublic) {
                $conditions[$joinName . '.public_flg'] = Label::PUBLIC_FLG_ON;
            }
            $query->join([
                'table' => 'labels',
                'alias' => $joinName,
                'type' => 'LEFT',
                'conditions' => $conditions,
            ]);

            if (count($columns) > 0) {
                $joinColumns = [];
                foreach ($columns as $column) {
                    $joinColumns[$column . '_' . $joinIndex] = $joinName . '.' . $column;
                }
                $query->select($joinColumns);
            }
        }

        return $query;
    }

    /**
     * 階層をまたいでラベルIDを検索する条件を追加
     *
     * @param \Cake\ORM\Query $query Query
     * @param int $labelId ラベルID
     * @return void
     */
    public function addNestWhere(Query $query, int $labelId)
    {
        $labelWhere = [];
        $labelLimit = Configure::readOrFail('Setting.label.depth');
        for ($i = 0; $i < $labelLimit; ++$i) {
            $table = 'Labels';
            if ($i > 0) {
                $table = 'labels_' . $i;
            }
            $labelWhere[$table . '.id'] = $labelId;
        }
        $query->where([
            'OR' => $labelWhere,
        ]);
    }

    /**
     * ラベル情報を階層ごとに分割
     *
     * @param array $data データ
     * @param array $columns カラム
     * @return array
     */
    public function splitLabelData($data, $columns)
    {
        $labelData = [];
        $limit = Configure::readOrFail('Setting.label.depth');
        for ($i = 0; $i < $limit; ++$i) {
            $index = Configure::readOrFail('Setting.label.depth') - $i - 1;
            if (isset($data['id_' . $index])) {
                foreach ($columns as $column) {
                    if (array_key_exists($column . '_' . $index, $data)) {
                        $labelData[$i][$column] = $data[$column . '_' . $index];
                    }
                }
            }
        }

        return array_values($labelData);
    }

    /**
     * @param mixed $labelId ラベルID
     * @return bool
     */
    public function hasLabelForUser($labelId)
    {
        if (!$this->validatePrimaryKey($labelId)) {
            return false;
        }

        if ($this->exists(['id' => $labelId, 'public_flg' => Label::PUBLIC_FLG_ON])) {
            $labels = $this->find('parents', ['inputs' => [
                'id' => $labelId,
            ]])->first();

            if (is_array($labels)) {
                if (ArrayUtility::arraySearch(Label::PUBLIC_FLG_OFF, array_column($labels, 'public_flg')) === false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 管理者が利用可能なカテゴリのIDを取得する
     *
     * @param bool $includeParents trueの場合は、担当カテゴリの上位カテゴリを含める
     * @param bool $excludeItself trueの場合は、担当カテゴリ自身は含めない
     * @return array
     */
    public function getAdminUsableLabelIds($includeParents = false, $excludeItself = false)
    {
        if (!$this->commonData()->existsAdminLoginData()) {
            return [];
        }

        $adminLabelId = $this->commonData()->getAdminLoginLabel();
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();
        if ($loginData->isMasterAdmin() || is_null($adminLabelId)) {
            return [];
        }

        $adminUsableLabelIds = [];

        // 担当カテゴリ自身
        if (!$excludeItself) {
            $adminUsableLabelIds[$adminLabelId] = $adminLabelId;
        }

        // 担当カテゴリの上位カテゴリ
        if ($includeParents) {
            $query = $this->find('parents', ['inputs' => ['id' => $adminLabelId]]);
            foreach ($query as $labelsByHierarchy) {
                foreach ($labelsByHierarchy as $label) {
                    $adminUsableLabelIds[$label['id']] = $label['id'];
                }
            }
        }

        // 担当カテゴリの下位カテゴリ
        $childrenLabels = $this->getChildrenDataById($adminLabelId);
        foreach (array_keys($childrenLabels) as $labelId) {
            $adminUsableLabelIds[$labelId] = $labelId;
        }

        return $adminUsableLabelIds;
    }

    /**
     * 管理者の担当カテゴリ（またはその下位カテゴリ）の場合はtrueを返す
     *
     * @param int|null $labelId ラベルID
     * @param bool $excludeItself trueの場合は、担当カテゴリ自身は操作不可とする
     * @return bool
     */
    public function isAdminUsableLabel($labelId, $excludeItself = false)
    {
        if (!$this->commonData()->existsAdminLoginData()) {
            return false;
        }

        $adminLabelId = $this->commonData()->getAdminLoginLabel();
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        // 管理者の担当ラベルが無い場合はカテゴリ制限を受けない(マスター管理者を除く)
        if ($loginData->isMasterAdmin() || is_null($adminLabelId)) {
            return true;
        }

        $adminUsableLabelIds = $this->getAdminUsableLabelIds(false, $excludeItself);

        if (isset($adminUsableLabelIds[$labelId])) {
            return true;
        }

        return false;
    }

    /**
     * 管理者に紐づかないカテゴリーで検索された場合、強制的に担当カテゴリで検索
     *
     * @param array $searchData 検索情報
     * @param string $labelIdName キー名
     * @return array
     */
    public function setSearchLabelID($searchData, $labelIdName = 'label_id')
    {
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();
        if ($loginData->isMasterAdmin()) {
            return $searchData;
        }

        if (array_key_exists($labelIdName, $searchData) && !$this->isAdminUsableLabel($searchData[$labelIdName])) {
            $searchData[$labelIdName] = $this->commonData()->getAdminLoginLabel();
        }

        return $searchData;
    }

    /**
     * ラベル検証用バリデータ
     * 管理者の担当カテゴリに紐づいているかを検証
     *
     * @param \Cake\Validation\Validator $validator validator
     * @param string $fieldName fieldName default 'label_id'
     * @return \Cake\Validation\Validator
     */
    public function addValidateLabelIdAdminUsable(Validator $validator, $fieldName = 'label_id')
    {
        $validator->add($fieldName, [
            'checkLabelId' => [
                'rule' => function ($check) {
                    return $this->isAdminUsableLabel((int)$check);
                },
                'last' => true,
                'message' => __(Message::ERROR_IN_LIST),
            ],
        ]);

        return $validator;
    }

    /**
     * 管理者に紐づくカテゴリが第３階層か判定(マスター管理者を除く)
     *
     * @return bool
     */
    public function isLowerLabelData()
    {
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();
        // マスター管理者またはカテゴリーが設定されていない場合
        if ($loginData->isMasterAdmin() || is_null($this->commonData()->getAdminLoginLabel())) {
            return false;
        }

        $adminLabelId = $this->commonData()->getAdminLoginLabel();
        $labelList = $this->find('parents', [
            'inputs' => [
                'id' => $adminLabelId,
            ],
        ])->first();
        // 親ラベルの個数から第３階層か判定(自身のラベル含む)
        if (is_array($labelList) && count($labelList) < Configure::readOrFail('Setting.label.depth')) {
            return false;
        }

        return true;
    }

    /**
     * 管理者が利用可能なカテゴリのIDを取得する(csvサンプル用)
     *
     * @return array
     */
    public function getAdminLabelIdList()
    {
        $adminLabelId = $this->commonData()->getAdminLoginLabel();
        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();
        // マスター管理者またはカテゴリが設定されていない場合は制限なし
        if ($loginData->isMasterAdmin() || is_null($adminLabelId)) {
            return $this->find('list', [
                'keyField' => 'id',
                'valueField' => 'name',
            ])->toArray();
        }

        $adminUsableLabelIds = [];

        // 担当ラベルの情報のみを取得
        $query = $this->find('parents', ['inputs' => ['id' => $adminLabelId]]);
        foreach ($query as $labelsByHierarchy) {
            foreach ($labelsByHierarchy as $label) {
                if ($label['id'] === $adminLabelId) {
                    $adminUsableLabelIds[$label['id']] = $label['name'];
                }
            }
        }

        // 下位ラベルを取得
        $childrenLabels = $this->getChildrenDataById($adminLabelId);
        foreach ($childrenLabels as $label) {
            $adminUsableLabelIds[$label['id']] = $label['name'];
        }

        return $adminUsableLabelIds;
    }

    /**
     * 編集時の初期データを設定
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @return mixed
     */
    public function formatDefault(EntityInterface $entity)
    {
        $labelAuthorities = $entity->get('label_authorities');

        $setLabelAuthorities = [];

        foreach ($labelAuthorities as $labelAuthority) {
            $setLabelAuthorities[$labelAuthority->get('user_authority_id')] = $labelAuthority;
        }

        $entity->set('label_authorities', $setLabelAuthorities);
    }

    /**
     * 会員の権限と同じ閲覧権限を持つカテゴリーIDを取得
     *
     * @return array
     */
    public function getLabelIdByUserAuthority()
    {
        if ($this->commonData()->existsUserLoginData()) {
            /** @var \App\Model\Table\LabelAuthoritiesTable $labelAuthoritiesTable */
            $labelAuthoritiesTable = $this->fetchTable('LabelAuthorities');

            $labelIds = [];
            $userAuthorityId = $this->commonData()->getUserLoginData()->get('user_authority_id');
            $labelId = $labelAuthoritiesTable->find()
                ->select(['label_id'])
                ->where(['user_authority_id' => $userAuthorityId])
                ->all()
                ->toArray();
            foreach ($labelId as $id) {
                $labelIds[] = $id->get('label_id');
            }

            return $labelIds;
        }

        return [];
    }
}
