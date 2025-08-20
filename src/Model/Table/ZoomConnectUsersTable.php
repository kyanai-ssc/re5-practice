<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\ZoomConnectUser;
use App\Utility\OAuth\Token;
use App\Utility\VideoMeeting\VideoMeetingFactory;
use ArrayObject;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Validation\Validator;

/**
 * ZoomConnectUsers Model
 *
 * @method \App\Model\Entity\ZoomConnectUser newEmptyEntity()
 * @method \App\Model\Entity\ZoomConnectUser newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ZoomConnectUser get($primaryKey, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ZoomConnectUser|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ZoomConnectUser[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ZoomConnectUsersTable extends AppTable
{
    public const NAME_MAXLENGTH = 100;
    public const CODE_MAXLENGTH = 100;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->getBehavior('AdminOperationLog')->setConfig('saveOperation', true);
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
                    'rule' => ['maxLength', static::NAME_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAXLENGTH),
                ],
            ]);

        $isCodeRequired = function ($context) {
            return $context['newRecord'];
        };

        $validator
            ->requirePresence('code', $isCodeRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString(
                'code',
                __(Message::ERROR_NOT_EMPTY),
                function ($context) use ($isCodeRequired) {
                    return !call_user_func($isCodeRequired, $context);
                }
            )
            ->add('code', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'message' => __(Message::ERROR_ALNUM_SYM),
                    'last' => true,
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CODE_MAXLENGTH],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CODE_MAXLENGTH),
                ],
            ]);

        return $validator;
    }

    /**
     * デフォルトのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefault(Query $query, array $options)
    {
        $query->select([
            'id',
            'name',
            'refresh_token',
            'access_token',
            'at_expiration_timestamp',
            'created',
            'modified',
        ]);
        $query->order([
            'ZoomConnectUsers.id' => 'DESC',
        ]);
        $query->limit(1);

        return $query;
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!($entity instanceof ZoomConnectUser)) {
            throw new CakeException();
        }

        if ((string)$entity->get('code') !== '') {
            $videoMeeting = VideoMeetingFactory::createZoomApiModule();
            $token = $videoMeeting->getAccessTokenByCode($entity->get('code'));
            if (!isset($token)) {
                $entity->setError('code', __(Message::ERROR_FAILED_TO_GET_REFRESH_TOKEN));

                return false;
            }

            $entity->set([
                'refresh_token' => $token->getRefreshToken(),
                'access_token' => $token->getAccessToken(),
                'at_expiration_timestamp' => $token->getExpiration(),
            ], ['guard' => false]);
        }

        return true;
    }

    /**
     * データを取得
     *
     * @return \App\Model\Entity\ZoomConnectUser|null データ
     */
    public function getData()
    {
        $data = $this->find('default')->first();
        if (!($data instanceof ZoomConnectUser)) {
            return null;
        }

        return $data;
    }

    /**
     * トークン更新処理
     *
     * @param \App\Model\Entity\ZoomConnectUser $entity エンティティ
     * @param \App\Utility\OAuth\Token $token トークン
     * @return void
     */
    public function updateToken(ZoomConnectUser $entity, Token $token)
    {
        $entity->set([
            'refresh_token' => $token->getRefreshToken(),
            'access_token' => $token->getAccessToken(),
            'at_expiration_timestamp' => $token->getExpiration(),
        ], ['guard' => false]);

        $this->saveOrFail($entity);
    }
}
