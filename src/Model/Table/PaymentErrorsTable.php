<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Http\ServerRequest;
use App\Model\AppTable;
use App\Model\Entity\PaymentError;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * PaymentErrors Model
 *
 * @method \App\Model\Entity\PaymentError newEmptyEntity()
 * @method \App\Model\Entity\PaymentError newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentError[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PaymentError get($primaryKey, $options = [])
 * @method \App\Model\Entity\PaymentError findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\PaymentError patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentError[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PaymentError|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentError saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\PaymentError[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentError[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentError[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\PaymentError[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PaymentErrorsTable extends AppTable
{
    /**
     * IPアドレス：MAX
     */
    public const IP_ADDRESS_MAX = 100;

    /**
     * @var \App\Model\Entity\PaymentError|null
     */
    protected $paymentError;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
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
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('ip_address', [
                'before' => true,
                'after' => true,
            ])
            ->callback('last_error_timestamp_from', [
                'callback' => function (Query $query, array $args) {
                    $query->where(['OR' => [
                        ['last_error_timestamp >=' => $args['last_error_timestamp_from']],
                        ['last_error_timestamp IS NULL'],
                    ]]);
                }])
            ->callback('last_error_timestamp_to', [
                'callback' => function (Query $query, array $args) {
                    $query->where(['OR' => [
                        ['last_error_timestamp <=' => $args['last_error_timestamp_to']],
                        ['last_error_timestamp IS NULL'],
                    ]]);
                }])
            ->callback('lock_timestamp', [
                'callback' => function (Query $query, array $args) {
                    $beforeTime = FrozenTime::now()->modify(
                        '-' . Configure::read('Setting.paymentError.lock.time') . ' minutes'
                    );
                    $data = $args['lock_timestamp'];
                    if (in_array(PaymentError::TYPE_LOCK, $data) && in_array(PaymentError::TYPE_UNLOCK, $data)) {
                        return;
                    } elseif (in_array(PaymentError::TYPE_LOCK, $data)) {
                        $query->where(['lock_timestamp >' => $beforeTime]);
                    } elseif (in_array(PaymentError::TYPE_UNLOCK, $data)) {
                        $query->where(['OR' => [
                            ['lock_timestamp <=' => $beforeTime],
                            ['lock_timestamp IS NULL'],
                        ]]);
                    }
                }]);
    }

    /**
     * 一覧のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findSearchList(Query $query, array $options)
    {
        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'desc'));

        $query
            ->order([
                    'PaymentErrors.' . $sort => $direction,
                ] + [
                    'PaymentErrors.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * ロック情報取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findLock(Query $query, array $options)
    {
        $query->select([
            'id',
            'ip_address',
            'error_count',
            'error_timestamp',
            'lock_timestamp',
            'last_error_timestamp',
            'all_error_count',
        ]);
        $query->where([
            'ip_address' => $options['ipAddress'],
        ]);

        return $query;
    }

    /**
     * 決済エラーを取得
     *
     * @return \App\Model\Entity\PaymentError
     */
    public function getData(): PaymentError
    {
        if (!isset($this->paymentError)) {
            $ipAddress = $this->getClientIp();

            $paymentError = $this->find('lock', [
                'ipAddress' => $ipAddress,
            ])->first();
            if (!($paymentError instanceof PaymentError)) {
                $paymentError = $this->createDefaultData($ipAddress);
            }

            $this->paymentError = $paymentError;
        }

        return $this->paymentError;
    }

    /**
     * 決済エラーを加算
     *
     * @return void
     */
    public function addError(): void
    {
        $paymentError = $this->getData();
        $paymentError->addErrorCount();

        $this->saveOrFail($paymentError);
    }

    /**
     * 決済エラーをリセット
     *
     * @return void
     */
    public function resetError(): void
    {
        $paymentError = $this->getData();
        if (
            $paymentError->isNew()
            || (!$paymentError->has('error_count') && !$paymentError->has('error_timestamp'))
        ) {
            return;
        }
        $paymentError->unlockPayment();

        $this->saveOrFail($paymentError);
    }

    /**
     * 初期登録データを生成
     *
     * @param string $ipAddress IPアドレス
     * @return \App\Model\Entity\PaymentError
     */
    protected function createDefaultData(string $ipAddress): PaymentError
    {
        return $this->newEntity([
            'ip_address' => $ipAddress,
            'error_count' => 0,
            'error_timestamp' => null,
            'lock_timestamp' => null,
            'last_error_timestamp' => null,
            'all_error_count' => 0,
        ], ['validate' => false]);
    }

    /**
     * 接続元IPを取得
     *
     * @return string
     */
    protected function getClientIp(): string
    {
        return (new ServerRequest())->clientIp();
    }
}
