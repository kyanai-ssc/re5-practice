<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\BounceMail;
use App\Model\Entity\MailDelivery;
use App\Model\Entity\MailDeliveryHistory;
use App\Model\Entity\User;
use ArrayObject;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenDate;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Throwable;

/**
 * MailDeliveryHistories Model
 *
 * @method \App\Model\Entity\MailDeliveryHistory newEmptyEntity()
 * @method \App\Model\Entity\MailDeliveryHistory newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory get($primaryKey, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MailDeliveryHistory[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MailDeliveryHistoriesTable extends AppTable
{
    use MailerAwareTrait;

    public const BOUNCE_MAIL_TOKEN_PREFIX = 'mail-';
    public const BOUNCE_MAIL_TOKEN_LENGTH = 32;

    public const MAIL_SEND_COUNT = 5000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('MailDeliveries', [
            'foreignKey' => 'mail_delivery_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
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
        //ステータスのみ更新
        if (!empty($options['updateStatus'])) {
            $entity->set('send_status', $options['updateStatus']);
        }
    }

    /**
     * メール履歴一覧での会員検索サブクエリ
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUser(Query $query, array $options)
    {
        //会員検索用の項目のみ
        $userOptions['inputs'] = $options['inputs'];
        unset($userOptions['inputs']['sort']);
        unset($userOptions['inputs']['direction']);
        unset($userOptions['inputs']['page']);
        unset($userOptions['inputs']['limit']);

        $usersTable = $this->getTableLocator()->get('Users');
        $userQuery = $usersTable->find('searchList', $userOptions);
        $userQuery->select(['Users.id'], true)
            ->group([
                'Users.id',
            ], true)
            ->contain([
                'UserAuthorities' => [
                    'fields' => [
                    ],
                ],
            ], true);

        $query->select(['mail_delivery_id'], true);

        $userWhere = $userQuery->clause('where');
        if (!empty($userWhere)) {
            $query->where(['user_id IN' => $userQuery]);
        }

        return $query;
    }

    /**
     * メール送信者を取得
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSendMailUser(Query $query, array $options)
    {
        $query->contain([
            'Users' => [
                'fields' => [
                    'id',
                    'login_id',
                    'mail',
                    'user_authority_id',
                    'expiration_date_from',
                    'expiration_date_to',
                    'created',
                    'Users__addition_values' => $this->driverExpression()->createJson([
                        'form_item_id' => 'form_item_id',
                        'value' => 'value',
                    ]),
                ],
            ],
            'Users.UserAuthorities' => [
                'fields' => [
                    'id',
                    'name',
                ],
            ],
            'Users.UserSmartLocks' => [
                'fields' => [
                    'id',
                    'user_id',
                    'smart_lock_user_id',
                ],
            ],
        ]);

        $bounceMailsTable = $this->getTableLocator()->get('BounceMails');
        $subQuery = $bounceMailsTable->find('all');
        $subQuery->select(['send_exclude_flg'], true)->distinct(['mail'])
            ->where('BounceMails.mail = Users.mail');

        $query->select([
            'MailDeliveryHistories.id',
            'MailDeliveryHistories.user_id',
            'MailDeliveryHistories.mail',
            'MailDeliveryHistories.mail_delivery_id',
            'MailDeliveryHistories.bounce_mail_token',
            'MailDeliveryHistories.send_status',
            'send_exclude_flg' => $subQuery,
        ]);

        $query->where([
            'Users.withdrawal_flg' => User::WITHDRAWAL_FLG_OFF,
            'mail_delivery_id' => $options['deliveryId'],
        ])->join([
            'table' => 'user_additions',
            'alias' => 'UserAdditions',
            'type' => 'LEFT',
            'conditions' => 'MailDeliveryHistories.user_id = UserAdditions.user_id',
        ]);

        $query->group(['MailDeliveryHistories.id', 'Users.id', 'UserAuthorities.id', 'UserSmartLocks.id']);
        $query->order('MailDeliveryHistories.id');
        $query->disableBufferedResults();

        return $query;
    }

    /**
     * メール配信処理
     *
     * @param \Cake\Datasource\EntityInterface $mailDelivery メール配信情報
     * @return void
     */
    public function sendMail(EntityInterface $mailDelivery)
    {
        $allCount = 0;
        $errorCount = 0;
        $page = 1;
        $lastPage = 1;
        while (true) {
            $paginator = new NumericPaginator();

            unset($sendUsers);
            $sendUsers = $paginator->paginate($this, [
                'page' => $page,
                'limit' => static::MAIL_SEND_COUNT,
            ], [
                'finder' => [
                    'sendMailUser' => [
                        'deliveryId' => $mailDelivery->get('id'),
                    ],
                ],
                'maxLimit' => static::MAIL_SEND_COUNT,
            ]);

            if ($page === 1) {
                $pagiData = $paginator->getPagingParams();
                $lastPage = $pagiData['MailDeliveryHistories']['pageCount'];
            }

            foreach ($sendUsers as $user) {
                try {
                    // メール配信
                    $this->sendMailUser($mailDelivery, $user);
                } catch (Throwable $e) {
                    try {
                        $this->getErrorLogger()->log($e, Router::getRequest());
                    } catch (Throwable $e2) {
                        // DO NOTHING
                    }
                    $errorCount += 1;
                }
                $allCount += 1;
            }

            if ($page >= $lastPage || empty($lastPage)) {
                break;
            }

            $page++;
        }

        // エラーメール送信
        if ($errorCount > 0) {
            /** @var \App\Mailer\ErrorMailer $errorMailer */
            $errorMailer = $this->getMailer('Error');

            $errorMailer->sendMailDeliveryErrorMail($mailDelivery->get('id'), $errorCount, $allCount);
        }
    }

    /**
     * メール配信情報の登録
     *
     * @param \Cake\Datasource\EntityInterface $mailDelivery 配信情報
     * @param \Cake\Datasource\EntityInterface $user 配信者情報
     * @return void
     */
    protected function sendMailUser($mailDelivery, $user)
    {
        if ($user->get('send_status') !== MailDelivery::SEND_STATUS_NOT_SEND) {
            return;
        }

        if ($user->get('send_exclude_flg') === BounceMail::SEND_EXCLUDE_FLG_ON) {
            $this->saveOrFail($user, [
                'validate' => false,
                'checkRules' => false,
                'updateStatus' => MailDeliveryHistory::SEND_STATUS_EXCLUDE,
            ]);

            return;
        }

        try {
            /** @var \App\Mailer\DefaultMailer $mail */
            $mail = $this->getMailer('Default');
            $mail->getMailDeliveryReplaceTokens();

            // 送信する現在のメールアドレスに更新
            $userData = $user->get('user')->toArray();
            $user->set('mail', $userData['mail']);

            $result = $this->getConnection()->transactional(function () use ($mailDelivery, $mail, $user) {
                $this->saveOrFail($user, [
                    'validate' => false,
                    'checkRules' => false,
                    'updateStatus' => MailDeliveryHistory::SEND_STATUS_SUCCESS,
                ]);

                // メール送信
                $mail = clone $mail;
                $mail->mailDelivery($mailDelivery, $user);

                return true;
            });
            if (!$result) {
                throw new CakeException();
            }
        } catch (Throwable $e) {
            try {
                $this->saveOrFail($user, [
                    'validate' => false,
                    'checkRules' => false,
                    'updateStatus' => MailDeliveryHistory::SEND_STATUS_FAILED,
                ]);
            } catch (Throwable $e2) {
                throw new CakeException($e2->__toString() . "\n" . $e->__toString());
            }
            throw $e;
        }
    }

    /**
     * 送信したメールのカウント
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSendCount(Query $query, array $options)
    {
        $from = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
        $from = $from->setDateTime($from->year, $from->month, $from->startOfMonth()->day, 0, 0);

        $to = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
        $to = $to->addMonths(1);
        $to = $to->setDateTime($to->year, $to->month, $to->startOfMonth()->day, 0, 0);

        $mailDelivery = $this->getTableLocator()->get('MailDeliveries');

        $subQuery = $mailDelivery->find('all')->select('id')->where([
            'send_status' => MailDelivery::SEND_STATUS_SENT,
            'send_date >= ' => $from,
            'send_date < ' => $to,
        ]);

        $query->select([
            'id',
        ])->where(
            [
                'mail_delivery_id IN ' => $subQuery,
            ]
        );
        $query->enableHydration(false);

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
            'user_id',
            'mail',
        ]);

        $bounceMailToken = Hash::get($options, 'inputs.bounce_mail_token');
        $query->where([
            'bounce_mail_token' => $bounceMailToken,
        ]);

        return $query;
    }

    /**
     * バウンスメールトークンを生成
     *
     * @return string
     */
    public function generateBounceMailToken()
    {
        $random = Security::randomString(static::BOUNCE_MAIL_TOKEN_LENGTH);
        $token = static::BOUNCE_MAIL_TOKEN_PREFIX . $random;

        return $token;
    }
}
