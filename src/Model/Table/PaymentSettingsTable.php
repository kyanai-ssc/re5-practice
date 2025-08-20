<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\PaymentSetting;
use App\Model\Entity\SiteSetting;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Validation\Validator;

/**
 * PaymentSettings Model
 *
 * @method \App\Model\Entity\PaymentSetting newEmptyEntity()
 * @method \App\Model\Entity\PaymentSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PaymentSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PaymentSettingsTable extends AppTable
{
    /**
     * @var \App\Model\Entity\PaymentSetting|null
     */
    protected $cacheData = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

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
        $schema->setColumnType('card_brand', 'json');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('three_d_secure_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('three_d_secure_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('three_d_secure_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys(Configure::readOrFail('Master.payment.credit.3DSecure'))],
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
            'payment_service',
            'environment',
            'shop_id',
            'merchant_id',
            'service_id',
            'cust_code_prefix',
            'order_id_prefix',
            'job_code',
            'three_d_secure_flg',
            'card_brand',
            'shop_password',
            'hash_key',
            'basic_auth_id',
            'basic_auth_password',
            'encrypt_key',
            'encrypt_iv',
        ]);
        $query->order([
            'PaymentSettings.id' => 'DESC',
        ]);
        $query->limit(1);

        return $query;
    }

    /**
     * 編集時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findEdit(Query $query, array $options)
    {
        $query->select([
            'id',
            'payment_service',
            'environment',
            'shop_id',
            'merchant_id',
            'service_id',
            'cust_code_prefix',
            'order_id_prefix',
            'job_code',
            'three_d_secure_flg',
            'card_brand',
            'shop_password',
            'hash_key',
            'basic_auth_id',
            'basic_auth_password',
            'encrypt_key',
            'encrypt_iv',
        ]);

        $query->where([
            'PaymentSettings.payment_service' => $options['inputs']['payment_service'],
        ]);

        if (isset($options['inputs']['shop_id'])) {
            $query->where([
                'PaymentSettings.shop_id' => $options['inputs']['shop_id'],
            ]);
        }
        if (isset($options['inputs']['merchant_id'])) {
            $query->where([
                'PaymentSettings.merchant_id' => $options['inputs']['merchant_id'],
            ]);
        }
        if (isset($options['inputs']['service_id'])) {
            $query->where([
                'PaymentSettings.service_id' => $options['inputs']['service_id'],
            ]);
        }

        return $query;
    }

    /**
     * 削除時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDelete(Query $query, array $options)
    {
        $query->select([
            'id',
            'payment_service',
            'shop_id',
            'merchant_id',
            'service_id',
        ]);

        $query->where([
            'PaymentSettings.payment_service' => $options['inputs']['payment_service'],
        ]);

        if (isset($options['inputs']['shop_id'])) {
            $query->where([
                'PaymentSettings.shop_id' => $options['inputs']['shop_id'],
            ]);
        }
        if (isset($options['inputs']['merchant_id'])) {
            $query->where([
                'PaymentSettings.merchant_id' => $options['inputs']['merchant_id'],
            ]);
        }
        if (isset($options['inputs']['service_id'])) {
            $query->where([
                'PaymentSettings.service_id' => $options['inputs']['service_id'],
            ]);
        }

        return $query;
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
        if (!($entity instanceof PaymentSetting)) {
            throw new CakeException();
        }

        if ($entity->isPaymentServiceGmo() && $entity->is3DSecureFlgOn()) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

            $siteSettingsTable->updateAll([
                'reservation_continuous_flg' => SiteSetting::COMMON_USE_FLG_OFF,
                'modified' => $this->commonData()->getNowDateTime(),
            ], []);
        } elseif ($entity->isPaymentServiceSb()) {
            /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
            $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

            $siteSettingsTable->updateAll([
                'reservation_continuous_flg' => SiteSetting::COMMON_USE_FLG_OFF,
                'reservation_edit_payment_flg' => SiteSetting::COMMON_USE_FLG_OFF,
                'modified' => $this->commonData()->getNowDateTime(),
            ], []);
        }
    }

    /**
     * Model.afterSaveCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSaveCommit(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!($entity instanceof PaymentSetting)) {
            throw new CakeException();
        }

        $this->deleteCacheData();

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        $siteSettingsTable->deleteCacheData();
    }

    /**
     * データを取得
     *
     * @return \App\Model\Entity\PaymentSetting|null データ
     */
    public function getData()
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->first();
            }, 'paymentSettings');
        }

        return $this->cacheData;
    }

    /**
     * データを取得(失敗時エラー)
     *
     * @return \App\Model\Entity\PaymentSetting データ
     */
    public function getDataOrFail()
    {
        $data = $this->getData();
        if (!isset($data)) {
            throw new CakeException();
        }

        return $data;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'paymentSettings');
        $this->cacheData = null;
    }
}
