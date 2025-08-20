<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Mailer\DefaultMailer;
use App\Model\AppTable;
use App\Model\Entity\MailDelivery;
use App\Model\Entity\MailDeliveryHistory;
use App\Model\Entity\User;
use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validation;
use Kuchen\Validation\Validation\Validator as KuchenValidator;
use Throwable;

/**
 * MailDeliveries Model
 *
 * @method \App\Model\Entity\MailDelivery newEmptyEntity()
 * @method \App\Model\Entity\MailDelivery newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MailDelivery[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailDelivery get($primaryKey, $options = [])
 * @method \App\Model\Entity\MailDelivery findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MailDelivery patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailDelivery[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailDelivery|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailDelivery saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailDelivery[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDelivery[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDelivery[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDelivery[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MailDeliveriesTable extends AppTable
{
    use MailerAwareTrait;

    public const MAIL_MAX = 254;
    public const FROM_NAME_MAX = 100;
    public const SUBJECT_MAX = 100;
    public const CONTENTS_MAX = 50000;

    /**
     * バルクンサートの区切り数
     */
    public const BULK_INSERT_COUNT = 5000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('MailDeliveryHistories', [
            'foreignKey' => 'mail_delivery_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('delivery_target', 'json');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'sendType' => Configure::readOrFail('Master.mailDelivery.sendType'),
            'contentType' => Configure::readOrFail('Master.common.mailFormatName'),
            'sendTime' => $this->get30minSeparatedTime(),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $defaultFieldValues = [
            'send_type' => MailDelivery::SEND_TYPE_IMMEDIATELY,
            'content_type' => DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT,
        ];

        return $defaultFieldValues;
    }

    /**
     * @inheritDoc
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(function (EntityInterface $entity) {
            if ($entity->get('send_type') === MailDelivery::SEND_TYPE_RESERVE) {
                // 現在より過去はエラー
                if (empty($entity->getError('send_date')) && empty($entity->getError('send_time'))) {
                    $nowDateTime = new FrozenTime($this->commonData()->getNowDateTime()->format('Y-m-d H:i'));
                    $setDateTime = new FrozenTime($entity->get('send_datetime')->format('Y-m-d H:i'));

                    if ($setDateTime <= $nowDateTime) {
                        $entity->setError('send_date', (string)__(Message::ERROR_PAST));

                        return false;
                    }
                }
            }

            return true;
        }, 'sendDateTimePastCheck');

        return $rules;
    }

    /**
     * beforeSave
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        //ステータスのみ変更を実施する
        if (!empty($options['updateStatus'])) {
            $entity->set('send_status', $options['updateStatus']);
        } else {
            $entity->set('send_status', MailDelivery::SEND_STATUS_NOT_SEND);
        }

        if (!empty($options['sendDateTime'])) {
            $entity->set('send_timestamp', $this->commonData()->getNowDateTime());
        }

        if (isset($options['saveNew'])) {
            if (!empty($options['deliveryTarget'])) {
                $entity->set('delivery_target', $this->createDeliverySearchInputs($options['deliveryTarget']));
            }

            if ($entity->get('send_type') === MailDelivery::SEND_TYPE_IMMEDIATELY) {
                $nowDate = $this->commonData()->getNowDateTime();
                $entity->set('send_date', $nowDate);
                $entity->set('send_time', $nowDate->format('H:i'));
            }
        }
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
        if (isset($options['saveNew'])) {
            $result = $this->saveSendMailUser($entity);
            if (!$result) {
                throw new CakeException(Message::ERROR_SYSTEM_ERROR);
            }
        }
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
        if (isset($data['send_type']) && $data['send_type'] != MailDelivery::SEND_TYPE_RESERVE) {
            $data->offsetSet('send_date', '');
            $data->offsetSet('send_time', '');
        }
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('from_mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('from_mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
                'isDkimDomain' => [
                    'rule' => ['isDkimDomain'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_AVAILABLE_DOMAIN),
                ],
            ]);

        $validator
            ->requirePresence('reply_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('reply_to')
            ->add('reply_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                ],
                'email' => [
                    'rule' => ['email'],
                    'last' => true,
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                ],
            ]);

        $validator
            ->requirePresence('from_mail_name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('from_mail_name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('from_mail_name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::FROM_NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::FROM_NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('subject', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('subject', __(Message::ERROR_NOT_EMPTY), false)
            ->add('subject', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SUBJECT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SUBJECT_MAX),
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
            ->requirePresence('send_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('send_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('send_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('sendType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('content_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('content_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('content_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('contentType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $nowDateTime = $this->commonData()->getNowDateTime();
        $nowDate = $nowDateTime->format('Y-m-d');

        $validator
            ->requirePresence('send_date', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('send_date', __(Message::ERROR_NOT_EMPTY), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                return !($validator->isValid('send_type')
                    && (string)Hash::get($context['data'], 'send_type')
                    === (string)MailDelivery::SEND_TYPE_RESERVE);
            })
            ->add('send_date', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'lessThan' => [
                    'rule' => ['compareDateTime', Validation::COMPARE_GREATER_OR_EQUAL, $nowDate],
                    'last' => true,
                    'message' => __(Message::ERROR_PAST),
                ],
            ]);

        $validator
            ->requirePresence('send_time', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyTime('send_time', __(Message::ERROR_NOT_EMPTY_SELECT), function ($context) use ($validator) {
                if (!($validator instanceof KuchenValidator)) {
                    throw new CakeException();
                }

                return !($validator->isValid('send_type')
                    && (string)Hash::get($context['data'], 'send_type') === (string)MailDelivery::SEND_TYPE_RESERVE);
            })
            ->add('send_time', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('sendTime'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * メール配信用会員のwhere設定を作成
     *
     * @param mixed $checkList チェック情報
     * @return array|bool $searchInputs where情報
     */
    public function createDeliverySearchInputs($checkList)
    {
        if (empty($checkList['checked'])) {
            return false;
        }

        $allCheck = Hash::get($checkList['checked'], 'allCheck');
        if ((string)$allCheck === (string)Configure::readOrFail('Master.common.listCheckId.check')) {
            $searchInputs = ['inputs' => $checkList['data']];
        } else {
            $searchInputs = ['inputs' => ['user_id' => $checkList['checked']]];
        }

        //退会会員は対象とならない
        $searchInputs['inputs']['withdrawal_flg'] = User::WITHDRAWAL_FLG_OFF;

        return $searchInputs;
    }

    /**
     * @param mixed $checkList チェック情報
     * @return int|false 会員取得情報が1以上
     */
    public function getUserList($checkList)
    {
        $searchInputs = $this->createDeliverySearchInputs($checkList);

        if (!$searchInputs || !is_array($searchInputs)) {
            return false;
        }

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $users = $usersTable->find('searchList', $searchInputs)->select(['id', 'mail'], true)
            ->where(['mail IS NOT NULL']);

        $usersCount = $users->count();
        if ($usersCount >= 1) {
            return $usersCount;
        }

        return false;
    }

    /**
     * 送信可能なメールを取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSend(Query $query, array $options)
    {
        $query->select(['id'])->where([
            'send_status NOT IN' => [MailDelivery::SEND_STATUS_SENT, MailDelivery::SEND_STATUS_CANCEL],
        ]);
        $query->enableHydration(false);

        return $query;
    }

    /**
     * 送信対象のメールを取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSendMail(Query $query, array $options)
    {
        $query->select([
            'id',
            'send_type',
            'send_date',
            'send_time',
            'from_mail_name',
            'from_mail',
            'reply_to',
            'content_type',
            'subject',
            'contents',
            'delivery_target',
            'send_status',
        ])->where(['send_status IN' => [MailDelivery::SEND_STATUS_NOT_SEND, MailDelivery::SEND_STATUS_SENDING]]);

        if (is_null($options['id'])) {
            $date = $this->commonData()->getNowDateTime()->format('Y-m-d H:i');

            $query->where([
                'send_type' => MailDelivery::SEND_TYPE_RESERVE,
                'send_date + send_time <=' => $date,
            ]);
        } else {
            $query->where([
                'id' => $options['id'],
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('send_type', [
                'multiValue' => true,
            ])
            ->value('send_status', [
                'multiValue' => true,
            ])
            ->compare('send_date_from', [
                'operator' => '>=',
                'fields' => 'send_date',
            ])
            ->compare('send_time_from', [
                'operator' => '>=',
                'fields' => 'send_time',
            ])
            ->compare('send_date_to', [
                'operator' => '<=',
                'fields' => 'send_date',
            ])
            ->compare('send_time_to', [
                'operator' => '<=',
                'fields' => 'send_time',
            ])
            ->like('from_mail', [
                'before' => true,
                'after' => true,
            ])
            ->like('reply_to', [
                'before' => true,
                'after' => true,
            ])
            ->like('from_mail_name', [
                'before' => true,
                'after' => true,
            ])
            ->like('subject', [
                'before' => true,
                'after' => true,
            ]);
    }

    /**
     * 送信数による制限
     *
     * @param int $sendCount 送信予定数
     * @return bool
     */
    public function canSendLimit(int $sendCount)
    {
        $limit = $this->getRestrictionMail();

        if (is_integer($limit)) {
            $mailDeliveryHistories = $this->getAssociation('MailDeliveryHistories')->getTarget();
            $nowCount = $mailDeliveryHistories->find('sendCount')->count();

            if ($nowCount + $sendCount > $limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * 一覧ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $query->select([
            'id',
            'send_type',
            'send_status',
            'send_date',
            'send_time',
            'send_timestamp',
            'from_mail',
            'reply_to',
            'from_mail_name',
            'subject',
        ])->contain(['MailDeliveryHistories' => function (Query $q) {
            $successCount = $q->newExpr()->case();
            $successCount
                ->when(['send_status' => MailDeliveryHistory::SEND_STATUS_SUCCESS])
                ->then(1);

            return $q->select([
                'MailDeliveryHistories.mail_delivery_id',
                'sendCount' => $q->func()->count('MailDeliveryHistories.id'),
                'successCount' => $q->func()->count($successCount),
            ])->group(['MailDeliveryHistories.mail_delivery_id'])
                ->order('MailDeliveryHistories.mail_delivery_id', true);
        },
        ]);

        $userQuery = $this->getTableLocator()->get('MailDeliveryHistories')->find('User', $options);
        if (!empty($userQuery->clause('where'))) {
            $query->where([
                'id IN' => $userQuery,
            ]);
        }

        $query->formatResults(function (\Cake\Collection\CollectionInterface $results) {
            return $results->map(function ($row) {
                $row['mail_delivery_histories'] = array_shift($row['mail_delivery_histories']);

                return $row;
            });
        });

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $firstSort = [
            'MailDeliveries.' . $sort => $direction,
        ];

        if ($sort === 'send_timestamp') {
            $firstSort = [
                'MailDeliveries.send_date' => $direction,
                'MailDeliveries.send_time' => $direction,
            ];
        }

        $query->order($firstSort + [
                'MailDeliveries.send_date' => $direction,
                'MailDeliveries.send_time' => $direction,
                'MailDeliveries.id' => $direction,
            ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * 予約配信時間取得（00:00～23:30）
     *
     * @return mixed
     */
    public function get30minSeparatedTime()
    {
        $sendTime = Configure::readOrFail('Master.mailDelivery.sendTime');

        $time = new FrozenTime('00:00');
        $timeList[$time->format('H:i')] = $time->format('H時i分');

        while ($time->format('H:i') != $sendTime['max']) {
            $time = $time->addMinutes((int)$sendTime['interval']);
            $timeList[$time->format('H:i')] = $time->format('H時i分');
        }

        return $timeList;
    }

    /**
     * メール配信情報の登録
     *
     * @param \Cake\Datasource\EntityInterface $mailDelivery メール配信情報
     * @return bool|mixed
     */
    public function saveSendMailUser(EntityInterface $mailDelivery)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        /** @var \App\Model\Table\MailDeliveryHistoriesTable $mailDeliveryHistoriesTable */
        $mailDeliveryHistoriesTable = $this->getAssociation('MailDeliveryHistories')->getTarget();

        //配信ユーザーリストの登録
        $result = true;
        $status = MailDeliveryHistory::SEND_STATUS_NOT_SEND;
        $page = 1;
        $lastPage = 1;
        $saveCount = 0;
        $usersCount = 0;
        while (true) {
            $paginator = new NumericPaginator();

            unset($sendUsers);
            $sendUsers = $paginator->paginate($usersTable, [
                'page' => $page,
                'limit' => static::BULK_INSERT_COUNT,
            ], [
                'finder' => [
                    'mailDeliveryUsers' => [
                        'target' => $mailDelivery->get('delivery_target'),
                    ],
                ],
                'maxLimit' => static::BULK_INSERT_COUNT,
            ]);

            if ($page === 1) {
                $pagiData = $paginator->getPagingParams();
                $lastPage = $pagiData['Users']['pageCount'];
                $usersCount = $pagiData['Users']['count'];

                if ($usersCount <= 0) {
                    return false;
                }
            }

            $saveData = [];
            foreach ($sendUsers as $sendUser) {
                $mailDeliveryHistory = $mailDeliveryHistoriesTable->newEntity([], ['validate' => false]);
                $mailDeliveryHistory->clean();

                //5000件ずつのバルクインサート
                $saveData[] = [
                    'mail_delivery_id' => $mailDelivery->get('id'),
                    'user_id' => $sendUser->get('id'),
                    'mail' => $sendUser->get('mail'),
                    'send_status' => $status,
                    'bounce_mail_token' => $mailDeliveryHistoriesTable->generateBounceMailToken(),
                    'created' => $this->commonData()->getNowDateTime(),
                    'modified' => $this->commonData()->getNowDateTime(),
                ];
            }

            $saveQuery = $mailDeliveryHistoriesTable->insertQuery()
                ->insert([
                    'mail_delivery_id',
                    'user_id',
                    'mail',
                    'send_status',
                    'bounce_mail_token',
                    'created',
                    'modified',
                ]);

            $saveQuery->clause('values')->setValues($saveData);
            $result = $saveQuery->execute();
            $saveCount += $result->rowCount();

            if ($page >= $lastPage || empty($lastPage)) {
                break;
            }

            $page++;
        }

        if ($saveCount !== $usersCount) {
            return false;
        }

        return $result;
    }

    /**
     * メール配信情報の登録
     *
     * @param \Cake\Datasource\EntityInterface $mailDelivery メール配信情報
     * @return void
     */
    protected function updateSendMailStatus(EntityInterface $mailDelivery)
    {
        //配信中ステータスの場合は登録済みのため登録処理を実施しない
        if ($mailDelivery->get('send_status') === MailDelivery::SEND_STATUS_SENDING) {
            return;
        }

        $this->saveOrFail($mailDelivery, [
            'validate' => false,
            'checkRules' => false,
            'updateStatus' => MailDelivery::SEND_STATUS_SENDING,
            'sendDateTime' => true,
        ]);
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
        $query->select([
            'id',
            'send_type',
            'send_status',
            'send_date',
            'send_time',
            'send_timestamp',
            'from_mail',
            'reply_to',
            'from_mail_name',
            'subject',
            'contents',
            'content_type',
        ])->contain(['MailDeliveryHistories' => function (Query $q) {
            $successCount = $q->newExpr()->case();
            $successCount
                ->when(['send_status' => MailDeliveryHistory::SEND_STATUS_SUCCESS])
                ->then(1);

            return $q->select([
                'MailDeliveryHistories.mail_delivery_id',
                'sendCount' => $q->func()->count('MailDeliveryHistories.id'),
                'successCount' => $q->func()->count($successCount),
            ])->group(['MailDeliveryHistories.mail_delivery_id'])
                ->order('MailDeliveryHistories.mail_delivery_id', true);
        },
        ]);

        $query->formatResults(function (CollectionInterface $results) {
            return $results->map(function ($row) {
                $row['mail_delivery_histories'] = array_shift($row['mail_delivery_histories']);

                return $row;
            });
        });

        return $query;
    }

    /**
     * メール配信処理
     *
     * @param int|null $id ID
     * @param array|null $options オプション
     * @return void
     */
    public function sendMail(?int $id = null, ?array $options = null)
    {
        $this->getLockForSendMail($id);

        /** @var \App\Model\Table\MailDeliveryHistoriesTable $mailDeliveryHistoriesTable */
        $mailDeliveryHistoriesTable = $this->getTableLocator()->get('MailDeliveryHistories');

        $mailDeliveryList = $this->find('sendMail', ['id' => $id]);

        foreach ($mailDeliveryList as $mailDelivery) {
            try {
                if (isset($options['onStart']) && is_callable($options['onStart'])) {
                    call_user_func($options['onStart'], $mailDelivery);
                }

                // 配信ステータスを配信中に更新
                $this->updateSendMailStatus($mailDelivery);

                $mailDeliveryHistoriesTable->sendMail($mailDelivery);

                // 配信ステータスを送信済みに更新
                $this->saveOrFail($mailDelivery, [
                    'validate' => false,
                    'checkRules' => false,
                    'updateStatus' => MailDelivery::SEND_STATUS_SENT,
                    'sendDateTime' => true,
                ]);

                if (isset($options['onEnd']) && is_callable($options['onEnd'])) {
                    call_user_func($options['onEnd'], $mailDelivery);
                }
            } catch (Throwable $e) {
                try {
                    $this->getErrorLogger()->log($e, Router::getRequest());

                    /** @var \App\Mailer\ErrorMailer $errorMailer */
                    $errorMailer = $this->getMailer('Error');

                    $errorMailer->sendMailDeliveryErrorMail($mailDelivery->get('id'));
                } catch (Throwable $e2) {
                    // DO NOTHING
                }
            }
        }

        $this->releaseLockForSendMail($id);
    }

    /**
     * メール配信処理のロックを取得
     *
     * @param int|null $mailDeliveryId メール配信ID
     * @return void
     */
    protected function getLockForSendMail($mailDeliveryId = null)
    {
        $lockCode = 0;
        if (isset($mailDeliveryId)) {
            $lockCode = $mailDeliveryId;
        }

        $this->getLock(static::LOCK_TYPE_MAIL_DELIVERY, $lockCode);
    }

    /**
     * メール配信処理のロックを解放
     *
     * @param int|null $mailDeliveryId メール配信ID
     * @return void
     */
    protected function releaseLockForSendMail($mailDeliveryId = null)
    {
        $lockCode = 0;
        if (isset($mailDeliveryId)) {
            $lockCode = $mailDeliveryId;
        }

        $this->releaseLock(static::LOCK_TYPE_MAIL_DELIVERY, $lockCode);
    }

    /**
     * メールの配信数制限
     *
     * @return int|null
     */
    public function getRestrictionMail()
    {
        /** @var \App\Model\Table\SystemSettingsTable $system */
        $system = $this->getTableLocator()->get('SystemSettings');
        $plan = $system->getData()->get('contract_plan');

        if (Configure::check('Client.restriction.mail')) {
            return Configure::readOrFail('Client.restriction.mail');
        } else {
            return Configure::read('Master.systemSetting.restriction.mail.' . $plan);
        }
    }
}
