<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\ReservationGuestCode;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * ReservationGuestCodes Model
 *
 * @method \App\Model\Entity\ReservationGuestCode newEmptyEntity()
 * @method \App\Model\Entity\ReservationGuestCode newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationGuestCode get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationGuestCode|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationGuestCode[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationGuestCodesTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Reservations', [
            'foreignKey' => 'reservation_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Model.beforeSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        //すでにトークンが存在する場合は再度トークンを
        $guestLogin = $this->find()->select('id')->where(['reservation_id' => $entity->get('reservation_id')])->first();
        if ($guestLogin instanceof EntityInterface) {
            $entity->set('id', $guestLogin->get('id'));
            $entity->setNew(false);
        }

        $code = '';
        $code = random_int(10000000, 99999999);

        $now = $this->commonData()->getNowDateTime();
        $expiration = new FrozenTime($now->format('Y/m/d H:i:s'));
        $expiration = $expiration->addHours(ReservationGuestCode::EXPIRATION_ADD_HOUR);

        $entity->set('code', $code);
        $entity->set('expiration_timestamp', $expiration);
    }

    /**
     * コード生成
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @param string $mail メールアドレス
     * @return bool|mixed
     * @throws \Exception
     */
    public function createCode(EntityInterface $entity, $mail)
    {
        $result = $this->getConnection()->transactional(function () use ($entity, $mail) {
            if (!$this->save($entity, ['validate' => false])) {
                return false;
            }

            /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
            $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');
            $autoReplyMailHistoriesTable->sendGuestLogin($entity, $mail);

            return true;
        });

        return $result;
    }

    /**
     * コード取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findCode(Query $query, array $options)
    {
        $query->select(['id', 'reservation_id', 'code'])
            ->contain(['Reservations' => [
                'fields' => ['id'],
            ]]);
        $query->where([
            'expiration_timestamp >=' => $this->commonData()->getNowDateTime(),
            'code' => Hash::get($options, 'code', 0),
            'Reservations.usage_timestamp_to >' => $this->CommonData()->getNowDateTime(),
        ]);

        return $query;
    }

    /**
     * 画面遷移時チェック
     *
     * @param int $reId 閲覧する予約ID
     * @param array|string|null $code 認証したコード
     * @return bool
     */
    public function existsReId($reId, $code)
    {
        $codeData = $this->find('code', [
            'code' => $code,
        ])->first();

        if (!$codeData instanceof EntityInterface) {
            return false;
        }

        if ((string)$codeData->get('reservation_id') !== (string)$reId) {
            return false;
        }

        return true;
    }
}
