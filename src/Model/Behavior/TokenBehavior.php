<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Locale\Message;
use App\ORM\Behavior;
use App\Utility\CommonData\CommonDataTrait;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Cake\Validation\Validator;

/**
 * Class RuleCheckingBehavior
 */
class TokenBehavior extends Behavior
{
    use CommonDataTrait;

    public const MAIL_MAX = 254;
    public const EXPIRATION_ADD_HOUR = 1;
    public const RANDOM_LENGTH = 16;

    protected $_defaultConfig = [
        'implementedFinders' => [
            'token' => 'findToken',
            'mail' => 'findMail',
        ],
        'implementedMethods' => [
            'tokenMailValidator' => 'tokenMailValidator',
            'validToken' => 'validToken',
        ],
        'expirationAddHour' => self::EXPIRATION_ADD_HOUR,
        'adminFlg' => false,
        'userFlg' => false,
        'mailConfirm' => false,
    ];

    /**
     * beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return \Cake\Datasource\EntityInterface|void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $table = $this->getTableLocator()->get($entity->getSource());

        if ($this->getConfig('adminFlg')) {
            $admin = $options['admin'];
            $entity->set('admin_id', $admin->get('id'));

            $adminMails = $admin->get('admin_mails');
            if (empty($adminMails)) {
                $event->stopPropagation();

                return $entity;
            }
            $adminMail = reset($adminMails);
            $entity->set('mail', $adminMail->get('mail'));

            $tokenEntity = $table->find('admin', ['adminId' => $entity->get('admin_id')]);
        } elseif ($this->getConfig('userFlg')) {
            /** @var \App\Model\Table\UsersTable $userTable */
            $userTable = $this->getTableLocator()->get('Users');

            $user = $userTable->find('reminder', ['mail' => $entity->get('mail')])->first();
            if (!($user instanceof EntityInterface)) {
                $event->stopPropagation();

                return $entity;
            }

            $entity->set('user_id', $user->get('id'));
            $options->offsetSet('user', $user);

            $tokenEntity = $table->find('user', ['userId' => $entity->get('user_id')]);
        } else {
            $tokenEntity = $table->find('mail', ['mail' => $entity->get('mail')]);
        }

        //すでにトークンが存在する場合は再度トークンを
        $tokens = $tokenEntity->first();
        if ($tokens instanceof EntityInterface) {
            $entity->set('id', $tokens->get('id'));
            $entity->setNew(false);
            $entity->setDirty('created', false);
        }

        $hash = Security::hash(
            $entity->get('mail') . Security::randomString(static::RANDOM_LENGTH),
            'sha256',
            true
        );

        $now = $this->commonData()->getNowDateTime();
        $expiration = new FrozenTime($now->format('Y/m/d H:i:s'));
        $expiration = $expiration->addHours($this->getConfig('expirationAddHour'));

        $entity->set('token', $hash);
        $entity->set('expiration_timestamp', $expiration);
    }

    /**
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator $validator validator
     */
    public function tokenMailValidator(Validator $validator)
    {
        $validator
            ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'email' => [
                    'rule' => ['email'],
                    'message' => __(Message::ERROR_MAIL_ADDRESS),
                    'last' => true,
                ],
            ]);

        if ($this->getConfig('mailConfirm')) {
            $validator
                ->requirePresence('mail_confirm', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('mail_confirm', __(Message::ERROR_NOT_EMPTY), false)
                ->add('mail_confirm', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'message' => __(Message::ERROR_INVALID_VALUE),
                        'last' => true,
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::MAIL_MAX],
                        'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                        'last' => true,
                    ],
                    'compareWith' => [
                        'rule' => ['compareWith', 'mail'],
                        'last' => true,
                        'message' => __(Message::ERROR_NOT_SAME),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * トークン取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMail(Query $query, array $options)
    {
        $query->select([
            'id',
            'mail',
            'token',
        ])->where([
            'mail' => Hash::get($options, 'mail', ''),
        ]);

        return $query;
    }

    /**
     * トークン取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findToken(Query $query, array $options)
    {
        $select = [];
        if ($this->commonData()->existsUserLoginData()) {
            $select = ['id', 'mail', 'token', 'user_id'];
        } else {
            $select = ['id', 'mail', 'token'];
        }

        $token = Hash::get($options, 'token', '');
        $query->select($select)->where([
            'token' => $token,
            'expiration_timestamp >=' => $this->commonData()->getNowDateTime(),
        ]);

        if ($this->getConfig('userFlg')) {
            $query->select(['id', 'user_id', 'token'], true)->contain([
                'Users' => [
                    'fields' => [
                        'id',
                    ],
                ],
            ]);
        }

        return $query;
    }
}
