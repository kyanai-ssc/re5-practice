<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\UserAuthority;
use App\Utility\ArrayUtility;
use App\Validation\CustomValidation;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * News Model
 *
 * @method \App\Model\Entity\News newEmptyEntity()
 * @method \App\Model\Entity\News newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\News[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\News get($primaryKey, $options = [])
 * @method \App\Model\Entity\News findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\News patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\News[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\News|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\News saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\News[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\News[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\News[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\News[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class NewsTable extends AppTable
{
    public const TITLE_MAX = 100;
    public const CONTENTS_MAX = 100000;
    public const SORT_NO_MAX = 10000000000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Labels', [
            'foreignKey' => 'label_id',
        ]);
        $this->hasMany('NewsAuthorities', [
            'foreignKey' => 'news_id',
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
        $newsAuthorities = $entity->get('news_authorities');

        $setNewsAuthorities = [];
        foreach ($newsAuthorities as $newsAuthority) {
            if ($newsAuthority->get('user_authority_id') === UserAuthority::SELECT_ALL) {
                $setNewsAuthorities = [];
                break;
            }

            if (!empty($newsAuthority->get('user_authority_id'))) {
                $setNewsAuthorities[] = $newsAuthority;
            }
        }

        $entity->set('news_authorities', $setNewsAuthorities);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        /** @var \App\Model\Table\NewsAuthoritiesTable $newsAuthoritiesTable */
        $newsAuthoritiesTable = $this->getTableLocator()->get('NewsAuthorities');

        $fieldValueOptions = [
            'userAuthorityId' => $newsAuthoritiesTable->getFieldValueOptions('userAuthorityId'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        $validator
            ->requirePresence('title', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('title', __(Message::ERROR_NOT_EMPTY), false)
            ->add('title', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::TITLE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TITLE_MAX),
                ],
            ]);

        $validator
            ->requirePresence('contents', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('contents', __(Message::ERROR_NOT_EMPTY), false)
            ->add('contents', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CONTENTS_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CONTENTS_MAX),
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
                    'rule' => ['naturalNumber', false],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, static::SORT_NO_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::SORT_NO_MAX),
                ],
            ]);

        $validator->requirePresence('public_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('public_from')
            ->add('public_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator->requirePresence('public_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('public_to')
            ->add('public_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'dateTime' => [
                    'rule' => [
                        'dateTime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => function ($value, $context) use ($validator) {
                        if ($validator->isValid('public_from') && $context['data']['public_from'] != '') {
                            return CustomValidation::compareDatetime(
                                $value,
                                CustomValidation::COMPARE_GREATER,
                                $context['data']['public_from']
                            );
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
            ]);

        $validator
            ->requirePresence('label_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        $validator
            ->requirePresence('news_authorities', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('news_authorities', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->array('news_authorities', __(Message::ERROR_INVALID_VALUE));

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->callback('user_authority_id', [
                'callback' => function ($query, $args) {
                    $orWhere = [];
                    $userAuthorities = Hash::get($args, 'user_authority_id');

                    $selectAll = ArrayUtility::arraySearch(UserAuthority::SELECT_ALL, $userAuthorities);
                    if ($selectAll !== false) {
                        unset($userAuthorities[$selectAll]);

                        $orWhere[] = function ($expression) {
                            /** @var \App\Model\Table\NewsAuthoritiesTable $newsAuthoritiesTable */
                            $newsAuthoritiesTable = $this->getTableLocator()->get('NewsAuthorities');

                            $newsAuthoritiesQuery = $newsAuthoritiesTable->find();
                            $newsAuthoritiesQuery->select(['NewsAuthorities.id']);
                            $newsAuthoritiesQuery->where([
                                'NewsAuthorities.news_id = News.id',
                            ]);
                            $expression->notExists($newsAuthoritiesQuery);

                            return $expression;
                        };
                    }

                    if (!empty($userAuthorities)) {
                        /** @var \App\Model\Table\NewsAuthoritiesTable $newsAuthoritiesTable */
                        $newsAuthoritiesTable = $this->getTableLocator()->get('NewsAuthorities');

                        $newsAuthoritiesQuery = $newsAuthoritiesTable->find();
                        $newsAuthoritiesQuery->select(['news_id']);
                        $newsAuthoritiesQuery->where([
                            'NewsAuthorities.user_authority_id IN' => $userAuthorities,
                        ]);
                        $orWhere[] = [
                            'News.id IN' => $newsAuthoritiesQuery,
                        ];
                    }

                    if (!empty($orWhere)) {
                        $query->where([
                            'OR' => $orWhere,
                        ]);
                    }
                },
            ])
            ->like('title', [
                'before' => true,
                'after' => true,
            ])
            ->callback('public_from', [
                'callback' => function (Query $query, $args) {
                    $query->where(['OR' => [
                        'News.public_to >=' => $args['public_from'],
                        'News.public_to IS NULL',
                    ]]);
                }])
            ->callback('public_to', [
                'callback' => function (Query $query, $args) {
                    $publicTo = new FrozenDate($args['public_to']);

                    $query->where(['OR' => [
                        'News.public_from <' => $publicTo->addDays(1),
                        'News.public_from IS NULL',
                    ]]);
                }]);

        $userAuthorities = $this->searchManager()->getFilters()->get('user_authority_id');

        //公開側用
        $this->searchManager()->useCollection('public');
        $this->searchManager()
            ->callback('label_id', ['callback' => function (Query $query, $args) {
                $query->where(['OR' => [
                    'label_id IN' => $args['label_id'],
                    'label_id IS NULL',
                ]]);
            }])
            ->callback('public_date', [
                'callback' => function (Query $query, $args) {
                    $query->where(['OR' => [
                        'public_from <=' => $args['public_date'],
                        'public_from IS NULL',
                    ]]);

                    $query->where(['OR' => [
                        'public_to >=' => $args['public_date'],
                        'public_to IS NULL',
                    ]]);
                }]);

        $this->searchManager()->getFilters('public')->offsetSet('user_authority_id', $userAuthorities);
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->select([
            'id',
            'title',
            'public_from',
            'public_to',
            'sort_no',
            'label_id',
        ])->contain([
            'NewsAuthorities' => [
                'fields' => ['id', 'news_id', 'user_authority_id'],
            ],
            'NewsAuthorities.UserAuthorities' => [
                'fields' => ['id', 'name'],
            ],
            'Labels' => [
                'fields' => ['id', 'name'],
            ],
        ]);

        $query->join([
            'Labels' => [
                'table' => 'labels',
                'type' => 'LEFT',
                'conditions' => 'Labels.id = Admins.label_id',
            ],
        ]);
        $labelsTable->joinQuery($query, 'Labels');

        $labelId = Hash::get($options, 'inputs.label_id');
        if (isset($labelId) && $labelId !== '') {
            $labelsTable->addNestWhere($query, (int)$labelId);
        }

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $query
            ->order([
                    'News.' . $sort => $direction,
                ] + [
                    'News.sort_no' => $direction,
                    'News.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 編集ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'label_id',
            'title',
            'contents',
            'public_from',
            'public_to',
            'sort_no',
        ]);
        $query->contain([
            'NewsAuthorities' => [
                'fields' => [
                    'id',
                    'news_id',
                    'user_authority_id',
                ],
            ],
        ]);

        return $query;
    }

    /**
     * 削除ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select(['id', 'label_id']);

        return $query;
    }

    /**
     * 詳細ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDetail(Query $query, array $options)
    {
        $query->find('edit');

        if (Hash::get($options, 'isAdmin', false)) {
            $search = [];
        } else {
            $accessUser = $this->commonData()->getAccessUserData();
            $search = [
                'label_id' => Hash::get($accessUser, 'labelId'),
                'user_authority_id' => [UserAuthority::SELECT_ALL, Hash::get($accessUser, 'userAuthorityId')],
                'public_date' => $this->commonData()->getNowDateTime(),
            ];
            $labelId = Hash::get($search, 'label_id');
            if (is_null($labelId)) {
                $query->where(['label_id IS NULL']);
            }
        }

        return $this->callFinder('search', $query, ['search' => $search, 'collection' => 'public']);
    }

    /**
     * 編集時の初期データを設定
     *
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @return mixed
     */
    public function formatDefault(EntityInterface $entity)
    {
        $newsAuthorities = $entity->get('news_authorities');

        $setNewsAuthorities = [];
        if (empty($newsAuthorities)) {
            $userAuthorityAll = $this->getAssociation('NewsAuthorities')->getTarget()->newEntity([
                'user_authority_id' => UserAuthority::SELECT_ALL,
            ]);

            $setNewsAuthorities[UserAuthority::SELECT_ALL] = $userAuthorityAll;
        }

        foreach ($newsAuthorities as $newsAuthority) {
            $setNewsAuthorities[$newsAuthority->get('user_authority_id')] = $newsAuthority;
        }

        $entity->set('news_authorities', $setNewsAuthorities);
    }

    /**
     * 公開側一覧取得用
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findPublicList(Query $query, array $options)
    {
        $accessUser = $this->commonData()->getAccessUserData();

        $query->select([
            'id',
            'title',
            'public_from',
            'public_to',
            'sort_no',
            'label_id',
        ])->contain([
            'NewsAuthorities' => [
                'fields' => ['id', 'news_id', 'user_authority_id'],
            ],
            'NewsAuthorities.UserAuthorities' => [
                'fields' => ['id', 'name'],
            ],
            'Labels' => [
                'fields' => ['id', 'name'],
            ],
        ]);

        $labelId = Hash::get($accessUser, 'labelId');
        if (is_null($labelId)) {
            $query->where(['label_id IS NULL']);
        }

        $search = [
            'label_id' => $labelId,
            'user_authority_id' => [UserAuthority::SELECT_ALL, Hash::get($accessUser, 'userAuthorityId')],
            'public_date' => $this->commonData()->getNowDateTime(),
        ];

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        $siteSettings = $siteSettingsTable->getData();
        if (Hash::get($options, 'top', false)) {
            $query->limit($siteSettings->get('top_news_number'));
        }

        $query = $this->callFinder('search', $query, ['search' => $search, 'collection' => 'public']);
        $query->order([
            'News.sort_no' => 'ASC',
            'News.public_from' => 'DESC',
            'News.id' => 'DESC',
        ], true);

        return $query;
    }
}
