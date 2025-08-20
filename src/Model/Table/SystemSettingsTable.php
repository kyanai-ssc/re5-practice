<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;

/**
 * SystemSettings Model
 *
 * @method \App\Model\Entity\SystemSetting newEmptyEntity()
 * @method \App\Model\Entity\SystemSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SystemSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SystemSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\SystemSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SystemSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SystemSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SystemSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SystemSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SystemSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SystemSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SystemSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SystemSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SystemSettingsTable extends AppTable
{
    /**
     * @var \App\Model\Entity\SystemSetting|null
     */
    protected $cacheData = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
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
            'contract_plan',
            'footer_logo_display_flg',
            'payment_use_flg',
            'admin_password_reset_day',
            'smart_lock_use_flg',
        ]);

        return $query;
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
        $this->deleteCacheData();
    }

    /**
     * データを取得
     *
     * @return \App\Model\Entity\SystemSetting データ
     */
    public function getData()
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->first();
            }, 'systemSettings');
        }

        return $this->cacheData;
    }

    /**
     * 契約プランを更新
     *
     * @param int $contractPlan 契約プラン
     * @return void
     */
    public function updateContractPlan(int $contractPlan)
    {
        $entity = $this->find('default')->first();
        if (!($entity instanceof EntityInterface)) {
            throw new CakeException();
        }

        $entity->clean();
        $entity->set('contract_plan', $contractPlan);
        $this->saveOrFail($entity);
    }

    /**
     * フッタロゴ表示設定を更新
     *
     * @param int $footerLogoDisplayFlg フッタロゴ表示設定
     * @return void
     */
    public function updateFooterLogoDisplayFlg(int $footerLogoDisplayFlg)
    {
        $entity = $this->find('default')->first();
        if (!($entity instanceof EntityInterface)) {
            throw new CakeException();
        }

        $entity->clean();
        $entity->set('footer_logo_display_flg', $footerLogoDisplayFlg);
        $this->saveOrFail($entity);
    }

    /**
     * 決済利用設定を更新
     *
     * @param int $paymentUseFlg 決済利用設定
     * @param array $paymentMethod 決済方法
     * @return void
     */
    public function updatePayment(int $paymentUseFlg, array $paymentMethod)
    {
        $entity = $this->find('default')->first();
        if (!($entity instanceof EntityInterface)) {
            throw new CakeException();
        }

        $entity->clean();
        $entity->set('payment_use_flg', $paymentUseFlg);

        $result = $this->getConnection()->transactional(function () use ($entity, $paymentMethod) {
            /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
            $paymentMethodsTable = $this->getTableLocator()->get('PaymentMethods');

            $paymentMethodsTable->updateDisplayFlg($paymentMethod);
            $this->saveOrFail($entity);

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
        Cache::delete('data', 'systemSettings');
        $this->cacheData = null;
    }
}
