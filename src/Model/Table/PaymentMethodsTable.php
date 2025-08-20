<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentSetting;
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
 * PaymentMethods Model
 *
 * @method \App\Model\Entity\PaymentMethod newEmptyEntity()
 * @method \App\Model\Entity\PaymentMethod newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentMethod[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentMethod get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentMethod findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PaymentMethod patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentMethod[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentMethod|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentMethod saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentMethod[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentMethod[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentMethod[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentMethod[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PaymentMethodsTable extends AppTable
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
            'foreignKey' => 'payment_method_id',
        ]);

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

        return $validator;
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
     * 決済文言取得ファインダー
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
            'display_flg',
        ]);
        $query->order([
            'PaymentMethods.sort_no' => 'ASC',
            'PaymentMethods.id' => 'ASC',
        ]);
        $query->enableHydration(false);

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
            }, 'paymentMethods');
        }

        return $this->cacheData;
    }

    /**
     * 決済方法の選択肢を取得
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
     * 表示設定された選択肢を取得
     *
     * @return array 選択肢
     */
    public function getDisplayValueOptions()
    {
        $valueOptions = [];
        foreach ($this->getData() as $data) {
            if (((string)$data['display_flg']) === ((string)PaymentMethod::DISPLAY_FLG_ON)) {
                $valueOptions[$data['id']] = $data['name'];
            }
        }

        return $valueOptions;
    }

    /**
     * 決済方法のIDを取得
     *
     * @param int $paymentMethodType 決済方法タイプ
     * @return array
     */
    public function getPaymentMethodIds(int $paymentMethodType)
    {
        $ids = [];
        foreach ($this->getData() as $data) {
            if ((string)$data['type'] === (string)$paymentMethodType) {
                $ids[] = (int)$data['id'];
            }
        }

        return $ids;
    }

    /**
     * 決済方法のタイプを取得
     *
     * @param int $paymentMethodId 決済方法ID
     * @return int タイプ
     */
    public function getPaymentMethodType(int $paymentMethodId)
    {
        foreach ($this->getData() as $data) {
            if (((string)$data['id']) === ((string)$paymentMethodId)) {
                return (int)$data['type'];
            }
        }
        throw new CakeException();
    }

    /**
     * 決済方法の名称を取得
     *
     * @param int $paymentMethodId 決済方法ID
     * @return string 名称
     */
    public function getPaymentMethodName(int $paymentMethodId)
    {
        $valueOptions = $this->getValueOptions();

        return $valueOptions[$paymentMethodId];
    }

    /**
     * 決済連携の要否を判定
     *
     * @param int $paymentMethodId 決済方法ID
     * @return bool
     */
    public function isRequiredPaymentLinkage(int $paymentMethodId)
    {
        return in_array(
            $this->getPaymentMethodType($paymentMethodId),
            PaymentMethod::REQUIRES_PAYMENT_LINKAGE_TYPE,
            true
        );
    }

    /**
     * API型決済の判定
     *
     * @param int $paymentMethodId 決済方法ID
     * @return bool
     */
    public function isApiPayment(int $paymentMethodId)
    {
        return in_array($this->getPaymentMethodType($paymentMethodId), PaymentMethod::API_PAYMENT_TYPE, true);
    }

    /**
     * リンク型決済の判定
     *
     * @param int $paymentMethodId 決済方法ID
     * @return bool
     */
    public function isLinkPayment(int $paymentMethodId)
    {
        return in_array($this->getPaymentMethodType($paymentMethodId), PaymentMethod::LINK_PAYMENT_TYPE, true);
    }

    /**
     * API型決済の利用判定
     *
     * @return bool
     */
    public function usesApiPayment()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!$systemSettingsTable->getData()->usePayment()) {
            return false;
        }
        foreach ($this->getData() as $data) {
            if (
                (string)$data['display_flg'] === (string)PaymentMethod::DISPLAY_FLG_ON
                && $this->isApiPayment((int)$data['id'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * 決済期限を取得
     *
     * @param int $paymentMethodId 決済方法ID
     * @param string|\Cake\I18n\FrozenTime|null $dateTime 基準日時(現在日時)
     * @return \Cake\I18n\FrozenTime
     */
    public function getPaymentLimit(int $paymentMethodId, $dateTime = null)
    {
        if (!isset($dateTime)) {
            $dateTime = $this->commonData()->getNowDateTime();
        }
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            throw new CakeException();
        }

        $configKey = 'Setting.payment.' . PaymentSetting::PAYMENT_SERVICE_SB;

        return $dateTime->addSeconds(
            Configure::readOrFail($configKey . '.paymentLimit.' . $this->getPaymentMethodType($paymentMethodId))
        );
    }

    /**
     * デフォルトのファインダー
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
            'display_flg',
        ]);
        $query->order([
            'PaymentMethods.sort_no' => 'ASC',
            'PaymentMethods.id' => 'ASC',
        ]);

        return $query;
    }

    /**
     * 表示設定を更新
     *
     * @param array $paymentMethod 決済方法
     * @return void
     */
    public function updateDisplayFlg(array $paymentMethod)
    {
        $result = $this->getConnection()->transactional(function () use ($paymentMethod) {
            $on = $paymentMethod;
            $off = array_diff(array_keys(Configure::readOrFail('Master.payment.method')), $on);

            if (!empty($off)) {
                $this->updateAll(
                    [
                        'display_flg' => PaymentMethod::DISPLAY_FLG_OFF,
                        'modified' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                    ],
                    [
                        'type IN' => $off,
                    ]
                );
            }
            if (!empty($on)) {
                $this->updateAll(
                    [
                        'display_flg' => PaymentMethod::DISPLAY_FLG_ON,
                        'modified' => $this->commonData()->getNowDateTime()->format('Y-m-d H:i:s'),
                    ],
                    [
                        'type IN' => $on,
                    ]
                );
            }

            return true;
        });
        $this->deleteCacheData();

        if (!$result) {
            throw new CakeException();
        }
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'paymentMethods');
        $this->cacheData = null;
    }
}
