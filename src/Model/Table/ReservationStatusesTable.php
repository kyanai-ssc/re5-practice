<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\ReservationStatus;
use App\Model\Table\Traits\WordTrait;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ReservationStatuses Model
 *
 * @method \App\Model\Entity\ReservationStatus newEmptyEntity()
 * @method \App\Model\Entity\ReservationStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservationStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservationStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReservationStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservationStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservationStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReservationStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReservationStatusesTable extends AppTable
{
    use WordTrait;

    public const WORD_MAX = 100;

    /**
     * @var array|null
     */
    protected $cacheData = null;

    /**
     * @var array|null
     */
    protected $valueOptions = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Events', [
            'foreignKey' => 'reservation_status_id',
        ]);
        $this->hasMany('Reservations', [
            'foreignKey' => 'reservation_status_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
            'afterSave' => false,
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
        //変更がない場合は更新日時を更新しない
        if (!$entity->isDirty('name') && !$entity->isDirty('sort_no')) {
            $entity->setDirty('modified', false);
        }
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'searchDisplayFlg' => Configure::read('Master.common.flg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->getWordValidator($validator, 'name', static::WORD_MAX);

        $validator
            ->requirePresence('search_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('search_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('search_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        $this->getFieldValueOptions('searchDisplayFlg'),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
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
            'status_type',
            'keep_stock_flg',
            'search_display_flg',
        ]);
        $query->order([
            'ReservationStatuses.sort_no' => 'ASC',
            'ReservationStatuses.id' => 'ASC',
        ]);
        $query->enableHydration(false);

        return $query;
    }

    /**
     * 文言取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWordEdit(Query $query, array $options)
    {
        $query->select([
            'ReservationStatuses.id',
            'ReservationStatuses.name',
            'ReservationStatuses.default_name',
            'ReservationStatuses.status_type',
            'ReservationStatuses.sort_no',
            'ReservationStatuses.search_display_flg',
        ])
            ->order([
                'ReservationStatuses.sort_no' => 'ASC',
                'ReservationStatuses.id' => 'ASC',
            ]);

        return $query;
    }

    /**
     * beforeSaveManyイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function beforeSaveMany(EventInterface $event, array $entities, $options)
    {
        $options->offsetSet('operationLogsId', []);
        foreach ($entities as $entity) {
            if (!$entity->isDirty('name') && !$entity->isDirty('sort_no')) {
                $entity->setDirty('modified', false);
            } else {
                $operationLogsId = Hash::get($options, 'operationLogsId', []);
                $operationLogsId[$entity->get('id')] = $entity;

                $options->offsetSet('operationLogsId', $operationLogsId);
            }
        }

        return true;
    }

    /**
     * Model.afterSaveManyCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSaveManyCommit(EventInterface $event, array $entities, ArrayObject $options)
    {
        $this->deleteCacheData();
    }

    /**
     * データを取得
     *
     * @return array データ
     */
    public function getData()
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->toArray();
            }, 'reservationStatuses');
        }

        return $this->cacheData;
    }

    /**
     * キャンセルのデータを取得
     *
     * @return array データ
     */
    public function getCancelData()
    {
        $data = null;
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['status_type']) === ((string)ReservationStatus::STATUS_TYPE_CANCEL)) {
                $data = $reservationStatus;
                break;
            }
        }

        return $data;
    }

    /**
     * キャンセル以外のデータを取得
     *
     * @return array データ
     */
    public function getNotCancelData()
    {
        $result = [];
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['status_type']) !== ((string)ReservationStatus::STATUS_TYPE_CANCEL)) {
                $result[] = $reservationStatus;
            }
        }

        return $result;
    }

    /**
     * 在庫を確保するデータを取得
     *
     * @return array データ
     */
    public function getKeepStockData()
    {
        $result = [];
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['keep_stock_flg']) === ((string)ReservationStatus::KEEP_STOCK_FLG_ON)) {
                $result[] = $reservationStatus;
            }
        }

        return $result;
    }

    /**
     * 予約ステータスの選択肢を取得
     *
     * @param bool $searchFlg 検索条件取得
     * @return array 選択肢
     */
    public function getValueOptions(bool $searchFlg = false)
    {
        if (!isset($this->valueOptions)) {
            $this->valueOptions = Hash::combine($this->getData(), '{*}.id', '{*}.name');
        }

        if ($searchFlg) {
            return Hash::combine(
                $this->getData(),
                '{*}[search_display_flg=' . Configure::read('Master.common.flg.on') . '].id',
                '{*}[search_display_flg=' . Configure::read('Master.common.flg.on') . '] . name'
            );
        }

        return $this->valueOptions;
    }

    /**
     * 予約ステータスの名称を取得
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return string 名称
     */
    public function getReservationStatusName(int $reservationStatusId)
    {
        $valueOptions = $this->getValueOptions();

        return $valueOptions[$reservationStatusId];
    }

    /**
     * 予約ステータスのタイプを取得
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return int タイプ
     */
    public function getReservationStatusType(int $reservationStatusId)
    {
        $type = null;
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['id']) === ((string)$reservationStatusId)) {
                $type = $reservationStatus['status_type'];
            }
        }

        return $type;
    }

    /**
     * 予約ステータスの在庫確保フラグを取得
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return int 在庫確保フラグ
     */
    public function getKeepStockFlg(int $reservationStatusId)
    {
        $keepStockFlg = null;
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['id']) === ((string)$reservationStatusId)) {
                $keepStockFlg = $reservationStatus['keep_stock_flg'];
            }
        }

        return $keepStockFlg;
    }

    /**
     * 公開側でのキャンセル可能判定
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return bool
     */
    public function canUserEditOrCancel(int $reservationStatusId)
    {
        $statusType = $this->getReservationStatusType($reservationStatusId);
        if (
            ((string)$statusType) !== ((string)ReservationStatus::STATUS_TYPE_FIXED)
            && ((string)$statusType) !== ((string)ReservationStatus::STATUS_TYPE_TENTATIVE)
        ) {
            return false;
        }

        return true;
    }

    /**
     * 公開側での締切関係なしのキャンセル可能判定
     *
     * @param int $reservationStatusId 予約ステータスID
     * @return bool
     */
    public function canUserEditOrCancelNoDeadline(int $reservationStatusId)
    {
        $statusType = $this->getReservationStatusType($reservationStatusId);
        $keepStockFlg = $this->getKeepStockFlg($reservationStatusId);
        if (
            ((string)$statusType) !== ((string)ReservationStatus::STATUS_TYPE_TENTATIVE)
            || ((string)$keepStockFlg) !== ((string)ReservationStatus::KEEP_STOCK_FLG_OFF)
        ) {
            return false;
        }

        return true;
    }

    /**
     * ステータスタイプをキーとした多次元配列データを返却
     *
     * @param array|null $type 取得したいステータスタイプ
     * @param bool $keyIsType trueの場合はタイプとステータスの連想配列
     * @param bool $keepOnly 在庫を消費するもののみ
     * @return mixed
     */
    public function getGroupingStatusType(?array $type = null, $keyIsType = true, $keepOnly = false)
    {
        $query = $this->find('all', [
            'order' => ['sort_no' => 'ASC', 'status_type' => 'ASC', 'id' => 'ASC'],
        ]);

        if ($type !== null) {
            $query->where(['status_type IN ' => $type]);
        }

        if ($keepOnly) {
            $query->where(['keep_stock_flg' => Configure::readOrFail('Master.common.flg.on')]);
        }

        $statusLists = [];
        foreach ($query as $row) {
            if ($keyIsType) {
                $statusLists[$row['status_type']][$row['id']] = $row;
            } else {
                $statusLists[$row['id']] = $row;
            }
        }

        return $statusLists;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'reservationStatuses');
        $this->cacheData = null;
    }

    /**
     * ステータスタイプからステータスIDを配列で取得
     *
     * @param int $reservationStatusType 予約ステータスタイプ
     * @return array ステータスIDの配列
     */
    public function getReservationStatusIds(int $reservationStatusType)
    {
        $ids = [];
        foreach ($this->getData() as $reservationStatus) {
            if (((string)$reservationStatus['status_type']) === ((string)$reservationStatusType)) {
                $ids[] = (string)$reservationStatus['id'];
            }
        }

        return $ids;
    }
}
