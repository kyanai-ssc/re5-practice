<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Table\Traits\WordTrait;
use App\Utility\DateTimeUtility;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ReceptionStatuses Model
 *
 * @method \App\Model\Entity\ReceptionStatus newEmptyEntity()
 * @method \App\Model\Entity\ReceptionStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ReceptionStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReceptionStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReceptionStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ReceptionStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReceptionStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReceptionStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReceptionStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReceptionStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReceptionStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReceptionStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ReceptionStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ReceptionStatusesTable extends AppTable
{
    use WordTrait;

    public const WORD_MAX = 100;

    /**
     * 受付ステータスが更新可能な間隔（秒）
     */
    public const RECEPTION_STATUS_UPDATE_INTERVAL = 60;

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

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
            'afterSave' => false,
        ]);
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
            'search_display_flg',
        ]);
        $query->order([
            'ReceptionStatuses.sort_no' => 'ASC',
            'ReceptionStatuses.id' => 'ASC',
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
            'ReceptionStatuses.id',
            'ReceptionStatuses.name',
            'ReceptionStatuses.default_name',
            'ReceptionStatuses.status_type',
            'ReceptionStatuses.sort_no',
            'ReceptionStatuses.search_display_flg',
        ])
            ->order([
                'ReceptionStatuses.sort_no' => 'ASC',
                'ReceptionStatuses.id' => 'ASC',
            ]);

        return $query;
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
            }, 'receptionStatuses');
        }

        return $this->cacheData;
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
     * 受付ステータスの選択肢を取得
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
     * 受付ステータスの名称を取得
     *
     * @param int $receptionStatusId 受付ステータスID
     * @return mixed 名称
     */
    public function getReceptionStatusName($receptionStatusId = null)
    {
        $valueOptions = $this->getValueOptions();

        if ($receptionStatusId === null) {
            return null;
        }

        return $valueOptions[$receptionStatusId];
    }

    /**
     * 受付ステータスのタイプを取得
     *
     * @param int $receptionStatusId 受付ステータスID
     * @return int タイプ
     */
    public function getReceptionStatusType(int $receptionStatusId)
    {
        $type = null;
        foreach ($this->getData() as $receptionStatuses) {
            if (((string)$receptionStatuses['id']) === ((string)$receptionStatusId)) {
                $type = $receptionStatuses['status_type'];
            }
        }

        return $type;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'receptionStatuses');
        $this->cacheData = null;
    }

    /**
     * ステータスタイプから受付ステータスの情報を取得
     *
     * @param string|int $type ステータスタイプ
     * @return array|null
     */
    public function getReceptionStatus($type)
    {
        foreach ($this->getData() as $receptionStatuses) {
            if (((string)$type) === ((string)$receptionStatuses['status_type'])) {
                return $receptionStatuses;
            }
        }

        return null;
    }

    /**
     * ステータスタイプから受付ステータスのIDを取得
     *
     * @param string|int $type ステータスタイプ
     * @return int|null
     */
    public function getReceptionStatusId($type)
    {
        foreach ($this->getData() as $receptionStatuses) {
            if (((string)$type) === ((string)$receptionStatuses['status_type'])) {
                return $receptionStatuses['id'];
            }
        }

        return null;
    }

    /**
     * 現在日時がインターバルを経過してるかどうか
     *
     * @param string|\DateTimeInterface $modified 更新日時
     * @return bool
     */
    public function checkUpdateInterval($modified)
    {
        $now = $this->commonData()->getNowDateTime();
        $modified = DateTimeUtility::convertToTimeObject($modified);
        if (!isset($modified)) {
            throw new CakeException();
        }
        $modified = $modified->addSeconds(self::RECEPTION_STATUS_UPDATE_INTERVAL * SECOND);

        return $modified < $now;
    }
}
