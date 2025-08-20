<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\Admin;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * AdminOperationalLogs Model
 *
 * @method \App\Model\Entity\AdminOperationalLog newEmptyEntity()
 * @method \App\Model\Entity\AdminOperationalLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminOperationalLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminOperationalLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminOperationalLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminOperationalLogsTable extends AppTable
{
    /**
     * CSV出力時の1回の取得件数
     */
    public const CSV_PAGEVIEW = 5000;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        $this->searchManager()
            ->value('authority', [
                'fields' => 'Admins.authority',
                'multiValue' => true,
            ])
            ->value('operated_function', [
                'multiValue' => true,
            ])
            ->compare('created_from', [
                'fields' => 'AdminOperationalLogs.created',
                'operator' => '>=',
            ])
            ->compare('created_to', [
                'fields' => 'AdminOperationalLogs.created',
                'operator' => '<=',
            ])
            ->like('login_id', [
                'before' => true,
                'after' => true,
            ]);
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
        $query->select([
            'admin_id',
            'AdminOperationalLogs.created',
            'AdminOperationalLogs.operated_function',
            'AdminOperationalLogs.operated_type',
            'AdminOperationalLogs.operated_data',
            'AdminOperationalLogs.login_id',
        ])->contain(['Admins' => [
            'fields' => ['id', 'authority'],
        ]]);

        $sort = Hash::get($options, 'inputs.sort', 'id');
        $direction = strtoupper(Hash::get($options, 'inputs.direction', 'asc'));

        $admin = $this->commonData()->getAdminLoginData();

        if ($admin['system_admin_flg'] !== Admin::SYSTEM_ADMIN_FLG_ON) {
            $query->where(['Admins.system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF]);
        }

        $query
            ->order([
                    'AdminOperationalLogs.' . $sort => $direction,
                ] + [
                    'AdminOperationalLogs.id' => $direction,
                ], true);

        return $this->callFinder('search', $query, ['search' => Hash::get($options, 'inputs', [])]);
    }

    /**
     * CSVファイルを生成
     *
     * @param array|string|null $searchCondition 検索条件
     * @return callable
     */
    public function createCsv($searchCondition)
    {
        $searchCondition = (array)$searchCondition;
        $header = Configure::readOrFail('Setting.csv.download.adminOperationalLog.header');

        $callback = $this->getCsvStreamCallback($header, function () use ($searchCondition) {
            $page = 1;
            $lastPage = 1;
            $searchCondition['limit'] = static::CSV_PAGEVIEW;

            // 取得時のデータを保持するためトランザクション開始
            // 一度でデータを取得するとメモリオーバーとなるため数件に分けて取得
            $connection = $this->getConnection();
            $connection->begin();
            while (true) {
                $searchCondition['page'] = $page;
                $paginator = new NumericPaginator();

                unset($query);
                $query = $paginator->paginate(
                    $this,
                    [
                        'page' => $page,
                        'limit' => static::CSV_PAGEVIEW,
                    ],
                    [
                        'finder' => [
                            'searchList' => [
                                'inputs' => $searchCondition,
                                'bufferOff' => true,
                            ],
                        ],
                        'maxLimit' => static::CSV_PAGEVIEW,
                    ]
                );

                foreach ($query as $adminOperationalLog) {
                    yield $this->generateCsvData($adminOperationalLog);
                }

                if ($page === 1) {
                    $pagingData = $paginator->getPagingParams();
                    $lastPage = $pagingData['AdminOperationalLogs']['pageCount'];
                }

                if (empty($lastPage) || $page >= $lastPage) {
                    break;
                }

                $page++;
            }
            // rollback
            $connection->rollback();
        });

        return $callback;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param \Cake\Datasource\EntityInterface $adminOperationalLog 操作ログデータ
     * @return array データ
     */
    protected function generateCsvData($adminOperationalLog)
    {
        $csv = [];
        $csv[] = $adminOperationalLog['created']->format('Y/m/d H:i:s');
        $csv[] = $adminOperationalLog['login_id'];
        $csv[] = Configure::readOrFail('Master.operation.function.' . $adminOperationalLog['operated_function']);
        $csv[] = Configure::readOrFail('Master.operation.type.' . $adminOperationalLog['operated_type']);
        $csv[] = $adminOperationalLog['operated_data'];

        return $csv;
    }

    /**
     * インポートのログを保存
     *
     * @param string $modelName モデル名
     * @param array $ids ID
     * @param int $insertCount 登録件数
     * @param int $updateCount 更新件数
     * @return void
     */
    public function saveImportLog(string $modelName, array $ids, int $insertCount, int $updateCount)
    {
        $data = Configure::readOrFail('Setting.operationalLog.import.default');
        if (ArrayUtility::inArray($modelName, Configure::readOrFail('Setting.csv.import.noModify'))) {
            $data = Configure::readOrFail('Setting.operationalLog.import.noModify');
        }

        $this->saveOperationalLogs($ids, [
            'controller' => $modelName,
            'action' => 'import',
            'data' => preg_replace(
                [
                    '/%insert_count%/',
                    '/%update_count%/',
                ],
                [
                    $insertCount,
                    $updateCount,
                ],
                $data
            ),
        ]);
    }
}
