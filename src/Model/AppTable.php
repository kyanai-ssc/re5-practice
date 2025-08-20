<?php
declare(strict_types=1);

namespace App\Model;

use App\Error\ErrorLoggerTrait;
use App\Locale\Message;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\Database\BuilderFactory;
use App\Validation\CustomValidation;
use ArrayObject;
use Cake\Core\Exception\CakeException;
use Cake\Database\Driver\Postgres;
use Cake\Database\Query as DatabaseQuery;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Utility\Security;
use Kuchen\Validation\ORM\Table;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Represents a single database table.
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method bool isSearch()
 * @method \Search\Manager searchManager()
 * @method void saveOperationalLogs($ids, array $option)
 * @method array extractEntity(\Cake\Datasource\EntityInterface $entity)
 * @method array excludeQueryByEntities($entities)
 * @method void setEntityErrors(\Cake\Datasource\EntityInterface $entity, array $errors, bool $overwrite = false)
 * @method bool checkEntityErrors(array $entities)
 * @method \Cake\Datasource\EntityInterface[] sortEntities($entities, $sortName = 'sort_no', $rebalance = false)
 */
class AppTable extends Table
{
    use CommonDataTrait;
    use CsvExportTrait;
    use ErrorLoggerTrait;
    use InputsTrait;
    use LocatorAwareTrait;

    public const LOCK_TYPE_UNIQUE_LOGIN_ID = 1;
    public const LOCK_TYPE_UNIQUE_MAIL = 2;
    public const LOCK_TYPE_MAIL_DELIVERY = 3;
    public const LOCK_TYPE_REMINDER_MAIL = 4;
    public const LOCK_TYPE_WAITING_CANCELLATION = 5;
    public const LOCK_TYPE_CSV_IMPORT = 6;
    public const LOCK_TYPE_USER_RESERVATION = 7;
    public const LOCK_TYPE_BOUNCE_MAIL = 8;
    public const LOCK_TYPE_EVENT_RESERVATION = 9;
    public const LOCK_TYPE_OPTION_RESERVATION = 10;
    public const LOCK_TYPE_SMART_LOCK = 11;
    public const LOCK_TYPE_ZOOM_CONNECT_USER = 12;
    public const LOCK_TYPE_THREE_D_SECURE = 13;

    /**
     * @var bool
     */
    protected $noAdditionalBehavior = false;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                    'modified' => 'always',
                ],
            ],
            'refreshTimestamp' => false,
        ]);
        if (!$this->noAdditionalBehavior) {
            $this->addBehavior('Search.Search');
            $this->addBehavior('AccessibleField');
            $this->addBehavior('AdminOperationLog');
            $this->addBehavior('Entity');
            $this->addBehavior('InputFilter');
            $this->addBehavior('RuleChecking');
            $this->addBehavior('AccountLock');
        }

        $this->timestamp($this->commonData()->getNowDateTime());
        $this->searchConfiguration();
    }

    /**
     * @inheritDoc
     */
    public function hasMany(string $associated, array $options = []): HasMany
    {
        $options += [
            'sort' => [
                $associated . '.id' => 'ASC',
            ],
        ];

        return parent::hasMany($associated, $options);
    }

    /**
     * @inheritDoc
     */
    public function save(EntityInterface $entity, $options = [])
    {
        if (Hash::get($options, 'alwaysUpdate', true)) {
            if (!$entity->isDirty('modified')) {
                $this->touch($entity);
            }
        }

        return parent::save($entity, $options);
    }

    /**
     * @inheritDoc
     */
    public function saveMany($entities, $options = [])
    {
        $options = new ArrayObject($options);

        $result = $this->getConnection()->transactional(function () use ($entities, $options) {
            $beforeSaveManyEvent = $this->dispatchEvent('Model.beforeSaveMany', [
                'entities' => $entities,
                'options' => $options,
            ]);
            if ($beforeSaveManyEvent->isStopped()) {
                return $beforeSaveManyEvent->getResult();
            }

            $result = parent::saveMany($entities, $options->getArrayCopy());

            $afterSaveManyEvent = $this->dispatchEvent('Model.afterSaveMany', [
                'entities' => $entities,
                'options' => $options,
            ]);
            if ($afterSaveManyEvent->isStopped()) {
                return $afterSaveManyEvent->getResult();
            }

            return $result;
        });

        $afterSaveManyCommitEvent = $this->dispatchEvent('Model.afterSaveManyCommit', [
            'entities' => $entities,
            'options' => $options,
        ]);
        if ($afterSaveManyCommitEvent->isStopped()) {
            return $afterSaveManyCommitEvent->getResult();
        }

        return $result;
    }

    /**
     * プライマリキーまとめて削除
     *
     * @param \Cake\ORM\Query $query entities
     * @param array|\ArrayAccess $options オプション
     * @return bool|mixed
     * @throws \Exception
     */
    public function deleteData(Query $query, $options = [])
    {
        $options = new ArrayObject($options);

        $result = $this->getConnection()->transactional(function () use ($query, $options) {
            //複数プライマリーは実施不可
            if (is_array($this->getPrimaryKey())) {
                throw new CakeException();
            }

            /** @var \App\Model\Table\TempDeletingIdsTable $tempDeletingIdsTable */
            $tempDeletingIdsTable = $this->getTableLocator()->get('TempDeletingIds');

            $tempDeletingIdsTable->createTempTable();

            // 一時テーブルへIDを登録
            $selectQuery = $this->getConnection()->selectQuery();
            $selectQuery->select([
                'id' => $this
                    ->getConnection()
                    ->getDriver()
                    ->quoteIdentifier($this->getAlias() . '__' . $this->getPrimaryKey()),
            ]);
            $selectQuery->from([
                'ids' => clone $query,
            ]);
            $insertQuery = $tempDeletingIdsTable->insertQuery()->insert(['id']);
            foreach ($selectQuery as $data) {
                $insertQuery->values(['id' => $data['id']]);
            }
            if (empty($insertQuery->clause('values')->getValues())) {
                // 0件の削除はエラー
                return false;
            }
            $insertQuery->execute();

            $idQuery = $tempDeletingIdsTable->find();
            $idQuery->select(['id']);

            $beforeDeleteData = $this->dispatchEvent('Model.beforeDeleteData', [
                'query' => $idQuery,
                'options' => $options,
            ]);

            if ($beforeDeleteData->isStopped()) {
                return $beforeDeleteData->getResult();
            }

            //アソシエーションの削除
            foreach ($this->associations() as $assoc) {
                if ($assoc->getDependent()) {
                    if (is_array($assoc->getForeignKey())) {
                        throw new CakeException();
                    }
                    $assoc->deleteAll([$assoc->getForeignKey() . ' IN' => $idQuery]);
                }
            }

            //サブクエリで削除
            $conditions = [$this->getPrimaryKey() . ' IN' => $idQuery];
            $result = $this->deleteAll($conditions);

            //0件の削除はエラー
            if ($result < 1) {
                return false;
            }

            $afterDeleteData = $this->dispatchEvent('Model.afterDeleteData', [
                'query' => $idQuery,
                'options' => $options,
            ]);

            if ($afterDeleteData->isStopped()) {
                return $afterDeleteData->getResult();
            }

            $tempDeletingIdsTable->dropTempTable();

            return true;
        });

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        $events = parent::implementedEvents();

        $eventMap = [
            'Model.afterMarshal' => [
                'callable' => 'afterMarshal',
                'priority' => 10,
            ],
            'Model.beforeSaveMany' => [
                'callable' => 'beforeSaveMany',
                'priority' => 10,
            ],
            'Model.afterSaveMany' => [
                'callable' => 'afterSaveMany',
                'priority' => 10,
            ],
            'Model.afterSaveManyCommit' => [
                'callable' => 'afterSaveManyCommit',
                'priority' => 10,
            ],
            'Model.afterUpdateMany' => [
                'callable' => 'afterUpdateMany',
                'priority' => 10,
            ],
            'Model.beforeDeleteData' => [
                'callable' => 'beforeDeleteData',
                'priority' => 10,
            ],
            'Model.afterDeleteData' => [
                'callable' => 'afterDeleteData',
                'priority' => 10,
            ],
        ];
        foreach ($eventMap as $event => $options) {
            if (method_exists($this, $options['callable'])) {
                $events[$event] = $options;
            }
        }

        return $events;
    }

    /**
     * 主キーを検証
     *
     * @param mixed $primaryKey 主キー
     * @return bool 検証結果
     */
    public function validatePrimaryKey($primaryKey)
    {
        $keys = (array)$this->getPrimaryKey();
        $values = (array)$primaryKey;
        if (count($keys) !== count($values)) {
            return false;
        }

        $validator = new KuchenValidator();
        foreach ($keys as $key) {
            $validator
                ->requirePresence($key, true)
                ->allowEmptyString($key, __(Message::ERROR_NOT_EMPTY), false)
                ->add($key, [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                    ],
                    'integer' => [
                        'rule' => ['integer', CustomValidation::BIGINT_MAX],
                        'last' => true,
                    ],
                ]);
        }

        $errors = array_combine($keys, $values);

        $errors = $validator->validate($errors);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * ドライバ固有のSQLを生成するビルダーを取得
     *
     * @return \App\Utility\Database\AbstractBuilder ビルダー
     */
    public function driverExpression()
    {
        $driverClass = namespaceSplit(get_class($this->getConnection()->getDriver()));

        return BuilderFactory::getInstance($driverClass[1]);
    }

    /**
     * シーケンスを初期化
     *
     * @param string|null $sequence シーケンス名
     * @return void
     */
    public function initializeSequence(?string $sequence = null)
    {
        if (!($this->getConnection()->getDriver() instanceof Postgres)) {
            return;
        }

        if (!isset($sequence)) {
            $sequence = $this->getTable() . '_id_seq';
        }

        $query = new DatabaseQuery($this->getConnection());
        $query->select([
            $this->driverExpression()->setSequenceValue($sequence, '1', false),
        ]);
        $query->execute();
    }

    /**
     * Searchプラグインの設定
     *
     * @return void
     */
    protected function searchConfiguration()
    {
    }

    /**
     * 配列データのキー振り直し
     *
     * @param array $reArray 配列
     * @param mixed $keys 振り直しを実施するキー
     * @param array $options オプション
     * @return array
     */
    public function rebalanceArrayKey(array $reArray, $keys, array $options = [])
    {
        foreach ((array)$keys as $key) {
            if (isset($reArray[$key]) && is_array($reArray[$key])) {
                $reArray[$key] = array_values($reArray[$key]);
            }
        }

        return $reArray;
    }

    /**
     * 複数のentityを特定の項目でソート
     *
     * @param array $inputs エンティティ
     * @param string $sortName ソートキー
     * @return array
     */
    public function addSortNo(array $inputs, $sortName = 'sort_no')
    {
        $sortNo = 1;
        foreach (array_keys($inputs) as $index) {
            $inputs[$index][$sortName] = $sortNo;
            $sortNo++;
        }

        return $inputs;
    }

    /**
     * ロックを試行する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return bool
     */
    protected function tryLock($type, $code)
    {
        $query = new DatabaseQuery($this->getConnection());
        $query->select([
            'result' => $this->driverExpression()->tryLock($type, $code),
        ]);

        $result = false;
        foreach ((array)$query->execute()->fetchAll('assoc') as $data) {
            if (isset($data['result']) && $data['result']) {
                $result = true;
            }
        }
        $this->releaseLock($type, $code);

        return $result;
    }

    /**
     * ロックを取得する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return void
     */
    protected function getLock($type, $code)
    {
        $query = new DatabaseQuery($this->getConnection());
        $query->select([
            'result' => $this->driverExpression()->getLock($type, $code),
        ]);
        $query->execute()->fetchAll();
    }

    /**
     * ロックを解放する
     *
     * @param int $type ロック種別
     * @param int $code ロックコード
     * @return void
     */
    protected function releaseLock($type, $code)
    {
        $query = new DatabaseQuery($this->getConnection());
        $query->select([
            'result' => $this->driverExpression()->releaseLock($type, $code),
        ]);
        $query->execute()->fetchAll();
    }

    /**
     * 値から勧告ロック用のコードを生成
     *
     * @param string $value ロック対象の値
     * @return int
     */
    protected function generateLockCode($value)
    {
        return (int)hexdec(substr(Security::hash($value, 'md5'), 0, 6));
    }
}
