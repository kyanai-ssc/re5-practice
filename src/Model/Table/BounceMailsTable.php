<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\AutoReplyMailHistory;
use App\Model\Entity\BounceMail;
use App\Model\Entity\MailDeliveryHistory;
use App\Utility\Mail\MailParser;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * BounceMails Model
 *
 * @method \App\Model\Entity\BounceMail newEmptyEntity()
 * @method \App\Model\Entity\BounceMail newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\BounceMail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BounceMail get($primaryKey, $options = [])
 * @method \App\Model\Entity\BounceMail findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\BounceMail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BounceMail[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BounceMail|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BounceMail saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BounceMail[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMail[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMail[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BounceMail[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class BounceMailsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('BounceMailHistories', [
            'foreignKey' => 'bounce_mail_id',
            'dependent' => true,
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
            'afterSave' => false,
        ]);
    }

    /**
     * beforeSave.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
    {
        if (isset($options['send_exclude_flg'])) {
            $entity->set('send_exclude_flg', $options['send_exclude_flg']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->callback('user_id', [
                'callback' => function (Query $query, array $args) {
                    /** @var \App\Model\Table\BounceMailHistoriesTable $bounceMailHistoriesTable */
                    $bounceMailHistoriesTable = $this->getTableLocator()->get('BounceMailHistories');
                    $bounceQuery = $bounceMailHistoriesTable->find('all');
                    $bounceQuery->select(['bounce_mail_id'], true)->where(['user_id' => $args['user_id']]);

                    $query
                        ->where(['BounceMails.id IN' => $bounceQuery]);
                },
            ])
            ->value('send_exclude_flg', [
                'multiValue' => true,
            ])
            ->like('mail', [
                'before' => true,
                'after' => true,
            ]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options = [])
    {
        $query->select(['BounceMails.id', 'mail', 'remaining_number', 'total_number', 'send_exclude_flg'])
            ->contain([
                'BounceMailHistories' => [
                    'fields' => ['bounce_mail_id', 'user_id'],
                ],
            ]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));
        $query->order([
                'BounceMails.' . $sort => $direction,
            ] + [
                'BounceMails.send_exclude_flg' => $direction,
                'BounceMails.mail' => $direction,
                'BounceMails.remaining_number' => $direction,
                'BounceMails.total_number' => $direction,
                'BounceMails.id' => $direction,
            ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * まとめて操作対象を取得
     *
     * @param array $checked チェック情報
     * @param array|string|null $searchInputs 検索条件
     * @return \Cake\ORM\Query
     */
    public function getTogetherBounceMails(array $checked, $searchInputs = null)
    {
        if (!is_array($searchInputs)) {
            $searchInputs = [];
        }

        $searchInputs['sort'] = 'id';
        $allCheck = Hash::get($checked, 'allCheck');
        if ((string)$allCheck === (string)Configure::readOrFail('Master.common.listCheckId.check')) {
            $query = $this->findSearchList($this->find('searchList'), ['inputs' => $searchInputs]);
        } else {
            $query = $this->findSearchList($this->find('all'))->where(['id IN' => $checked]);
        }

        return $query;
    }

    /**
     * 詳細用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDetail(Query $query, array $options = [])
    {
        $query->select(['id', 'mail', 'remaining_number', 'total_number', 'send_exclude_flg'])
            ->contain([
                'BounceMailHistories' => [
                    'fields' => ['id', 'bounce_mail_id', 'user_id'],
                ],
            ]);

        return $query;
    }

    /**
     * 削除用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options = [])
    {
        $query->find('detail');

        return $query;
    }

    /**
     * バウンスメール登録用のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findBounceMail(Query $query, array $options = [])
    {
        $query->select([
            'id',
            'mail',
            'total_number',
            'remaining_number',
            'send_exclude_flg',
        ]);

        $mail = Hash::get($options, 'inputs.mail');
        $query->where([
            'mail' => $mail,
        ]);

        $forUpdate = Hash::get($options, 'forUpdate', false);
        if ($forUpdate) {
            $query->epilog('FOR UPDATE');
        }

        return $query;
    }

    /**
     * 送信除外判定用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCheckExclude(Query $query, array $options = [])
    {
        $query->select([
            'id',
        ]);

        $mail = Hash::get($options, 'inputs.mail');
        $query->where([
            'mail' => $mail,
            'send_exclude_flg' => BounceMail::SEND_EXCLUDE_FLG_ON,
        ]);

        return $query;
    }

    /**
     * send_exclude_flgの一括更新処理
     *
     * @param \Cake\ORM\Query $query Query
     * @param int $sendExcludeFlg 送信フラグ
     * @param array $options オプション
     * @return bool|mixed
     * @throws \Exception
     */
    public function updateSendExcludeMany(Query $query, int $sendExcludeFlg, $options = [])
    {
        $options = new ArrayObject($options);

        $result = $this->getConnection()->transactional(function () use ($query, $sendExcludeFlg, $options) {

            //複合インデックスはエラー
            if (is_array($this->getPrimaryKey())) {
                throw new CakeException();
            }

            //サブクエリ用にIDのみのSQLを生成
            $statement = $this->getConnection()->selectQuery()
                ->select([
                    'id' => $this
                        ->getConnection()
                        ->getDriver()
                        ->quoteIdentifier($this->getAlias() . '__' . $this->getPrimaryKey()),
                ])
                ->from(['ids' => $query]);

            //サブクエリでUPDATE
            $update = [
                'send_exclude_flg' => $sendExcludeFlg,
                'modified' => $this->commonData()->getNowDateTime(),
            ];

            $conditions = [$this->getPrimaryKey() . ' IN' => $statement];
            $result = $this->updateAll($update, $conditions);

            $afterUpdateMany = $this->dispatchEvent('Model.afterUpdateMany', [
                'query' => $statement,
                'options' => $options,
            ]);

            if ($afterUpdateMany->isStopped()) {
                return $afterUpdateMany->getResult();
            }

            return $result;
        });

        return $result;
    }

    /**
     * メール内容からバウンスメールトークンを取得
     *
     * @param string $message メール内容
     * @return string|null
     */
    public function parseMail(string $message)
    {
        $mailParser = new MailParser($message);
        $address = $mailParser->getTo();
        if (!isset($address)) {
            return null;
        }

        $bounceMailToken = preg_replace('/@.+$/', '', $address);

        return $bounceMailToken;
    }

    /**
     * バウンスメールを保存
     *
     * @param string $bounceMailToken バウンスメールトークン
     * @param string $message メール内容
     * @return void
     */
    public function saveBounceMail(string $bounceMailToken, string $message)
    {
        $history = $this->getUserDataByToken($bounceMailToken);
        if (!isset($history)) {
            return;
        }

        $mail = null;
        $userId = null;
        if ($history instanceof AutoReplyMailHistory) {
            $mail = $history->get('user_mail');
            $userId = $history->get('user_id');
        } elseif ($history instanceof MailDeliveryHistory) {
            $mail = $history->get('mail');
            $userId = $history->get('user_id');
        }

        $lockCode = $this->getLockForBounceMail($mail);

        $this->getConnection()->transactional(function () use ($mail, $userId, $message) {
            $entity = $this->generateData($mail, $userId, $message);
            if (!isset($entity)) {
                return false;
            }
            $this->saveOrFail($entity);

            return true;
        });

        $this->releaseLockForBounceMail($lockCode);
    }

    /**
     * バウンスメールトークンから会員データを取得
     *
     * @param string $bounceMailToken バウンスメールトークン
     * @return \Cake\Datasource\EntityInterface|null
     */
    protected function getUserDataByToken(string $bounceMailToken)
    {
        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
        /** @var \App\Model\Table\MailDeliveryHistoriesTable $mailDeliveryHistoriesTable */
        $mailDeliveryHistoriesTable = $this->getTableLocator()->get('MailDeliveryHistories');

        $tables = [
            AutoReplyMailHistoriesTable::BOUNCE_MAIL_TOKEN_PREFIX => $autoReplyMailHistoriesTable,
            MailDeliveryHistoriesTable::BOUNCE_MAIL_TOKEN_PREFIX => $mailDeliveryHistoriesTable,
        ];
        $entity = null;
        foreach ($tables as $prefix => $table) {
            if (preg_match('/' . preg_quote($prefix, '/') . '/', (string)$bounceMailToken) === 1) {
                $entity = $table->find('bounceMail', [
                    'inputs' => [
                        'bounce_mail_token' => $bounceMailToken,
                    ],
                ])->first();
                break;
            }
        }
        if (!($entity instanceof EntityInterface)) {
            return null;
        }

        return $entity;
    }

    /**
     * エンティティを生成
     *
     * @param string $mail メールアドレス
     * @param int $userId 会員ID
     * @param string $message メール内容
     * @return \App\Model\Entity\BounceMail|null
     */
    protected function generateData($mail, $userId, $message)
    {
        /** @var \App\Model\Table\BounceMailHistoriesTable $bounceMailHistoriesTable */
        $bounceMailHistoriesTable = $this->getTableLocator()->get('BounceMailHistories');

        $entity = $this->find('bounceMail', [
            'inputs' => [
                'mail' => $mail,
            ],
            'forUpdate' => true,
        ])->first();
        if (!isset($entity)) {
            $entity = $this->newEntity([
                'mail' => $mail,
                'total_number' => 0,
                'remaining_number' => BounceMail::REMAIN_NUMBER,
                'send_exclude_flg' => BounceMail::SEND_EXCLUDE_FLG_OFF,
            ], ['validate' => false]);
        }
        if (!($entity instanceof BounceMail)) {
            throw new CakeException();
        }

        $entity->set('total_number', $entity->get('total_number') + 1);
        if ((string)$entity->get('send_exclude_flg') === ((string)BounceMail::SEND_EXCLUDE_FLG_OFF)) {
            $entity->set('remaining_number', $entity->get('remaining_number') - 1);
            if ($entity->get('remaining_number') <= 0) {
                $entity->set('remaining_number', BounceMail::REMAIN_NUMBER);
                $entity->set('send_exclude_flg', BounceMail::SEND_EXCLUDE_FLG_ON);
            }
        }
        $entity->set('bounce_mail_histories', [
            $bounceMailHistoriesTable->newEntity([
                'user_id' => $userId,
                'contents' => $message,
            ]),
        ]);

        return $entity;
    }

    /**
     * バウンスメール登録用のロックを取得
     *
     * @param string $mail メールアドレス
     * @return int
     */
    protected function getLockForBounceMail($mail)
    {
        $lockCode = $this->generateLockCode($mail);

        $this->getLock(static::LOCK_TYPE_BOUNCE_MAIL, $lockCode);

        return $lockCode;
    }

    /**
     * バウンスメール登録用のロックを解放
     *
     * @param int $lockCode ロックコード
     * @return void
     */
    protected function releaseLockForBounceMail($lockCode)
    {
        $this->releaseLock(static::LOCK_TYPE_BOUNCE_MAIL, $lockCode);
    }
}
