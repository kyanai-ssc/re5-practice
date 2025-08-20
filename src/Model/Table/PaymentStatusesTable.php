<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentStatus;
use App\Model\Table\Traits\WordTrait;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * PaymentStatuses Model
 *
 * @method \App\Model\Entity\PaymentStatus newEmptyEntity()
 * @method \App\Model\Entity\PaymentStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentStatus get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PaymentStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PaymentStatusesTable extends AppTable
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

        $this->hasMany('Reservations', [
            'foreignKey' => 'payment_status_id',
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
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->getWordValidator($validator, 'name', static::WORD_MAX);

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
            'type',
            'name',
        ]);
        $query->order([
            'PaymentStatuses.sort_no' => 'ASC',
            'PaymentStatuses.id' => 'ASC',
        ]);
        $query->enableHydration(false);

        return $query;
    }

    /**
     * 決済ステータス取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWordEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'type',
            'name',
        ]);
        $query->order([
            'PaymentStatuses.sort_no' => 'ASC',
            'PaymentStatuses.id' => 'ASC',
        ]);

        return $query;
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
            }, 'paymentStatuses');
        }

        return $this->cacheData;
    }

    /**
     * タイプからデータを取得
     *
     * @param int $type タイプ
     * @return array データ
     */
    public function getDataByDefaultType($type)
    {
        $result = null;
        foreach ($this->getData() as $data) {
            if (((string)$data['type']) === ((string)$type)) {
                $result = $data;
                break;
            }
        }

        return $result;
    }

    /**
     * 決済ステータスの選択肢を取得
     *
     * @return array 選択肢
     */
    public function getValueOptions()
    {
        if (!isset($this->valueOptions)) {
            $this->valueOptions = Hash::combine($this->getData(), '{*}.id', '{*}.name');
        }

        return $this->valueOptions;
    }

    /**
     * 決済ステータスの名称を取得
     *
     * @param int $paymentStatusId 決済ステータスID
     * @return string 名称
     */
    public function getPaymentStatusName(int $paymentStatusId)
    {
        $valueOptions = $this->getValueOptions();

        return $valueOptions[$paymentStatusId];
    }

    /**
     * 公開側での編集可能判定
     *
     * @param int|null $paymentMethodId 決済方法ID
     * @param int|null $paymentStatusId 決済ステータスID
     * @return bool
     */
    public function canUserEdit(?int $paymentMethodId = null, ?int $paymentStatusId = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (((string)$paymentMethodId) !== '') {
            if (((string)$paymentMethodId) === ((string)PaymentMethod::TYPE_CARD)) {
                if ($siteSettingsTable->getData()->isUseFlgOn('reservation_edit_payment_flg')) {
                    return true;
                } else {
                    return false;
                }
            }

            if (
                ((string)$paymentStatusId) !== ''
                && ((string)$paymentStatusId) !== ((string)PaymentStatus::TYPE_RECEIVE_YET)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'paymentStatuses');
        $this->cacheData = null;
    }
}
