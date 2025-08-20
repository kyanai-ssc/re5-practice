<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\TempAccessSummary;
use App\Utility\Csv\CsvReader;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * TempAccessSummaries Model
 *
 * @method \App\Model\Entity\TempAccessSummary newEmptyEntity()
 * @method \App\Model\Entity\TempAccessSummary newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\TempAccessSummary[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TempAccessSummary get($primaryKey, $options = [])
 * @method \App\Model\Entity\TempAccessSummary findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\TempAccessSummary patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TempAccessSummary[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TempAccessSummary|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TempAccessSummary saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TempAccessSummary[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TempAccessSummary[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\TempAccessSummary[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\TempAccessSummary[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class TempAccessSummariesTable extends AppTable
{
    public const DELETE_DAYS = 7;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * アクセス取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAccess(Query $query, array $options)
    {
        $accessDate = DateTimeUtility::convertToDateObject(Hash::get($options, 'inputs.access_date'));
        if (!isset($accessDate)) {
            throw new CakeException();
        }

        $query->select([
            'calendar' => $query->func()->sum('calendar'),
        ]);
        $query->where([
            'access_date' => $accessDate->format('Y-m-d'),
        ]);

        return $query;
    }

    /**
     * 集計Entityの作成
     *
     * @param string|\DateTimeInterface $accessDate 日付
     * @return \App\Model\Entity\TempAccessSummary|null
     */
    public function createEntity($accessDate)
    {
        $accessDate = DateTimeUtility::convertToDateObject($accessDate);
        if (!isset($accessDate)) {
            throw new CakeException();
        }

        $access = $this->countAccess($accessDate);
        if (!isset($access)) {
            return null;
        }

        $entity = $this->newEntity([
            'access_date' => new FrozenDate($accessDate->format('Y-m-d')),
            'calendar' => $access,
        ], ['validate' => false]);

        return $entity;
    }

    /**
     * 不要データの削除
     *
     * @return void
     */
    public function deleteOldData()
    {
        $date = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
        $date = $date->subDays(static::DELETE_DAYS);
        $this->deleteAll([
            'access_date <' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * アクセスログの集計
     *
     * @param string|\DateTimeInterface $date 集計日
     * @return int|null
     */
    protected function countAccess($date)
    {
        $date = DateTimeUtility::convertToDateObject($date);
        if (!isset($date)) {
            throw new CakeException();
        }

        $fileName = ACCESS_LOGS . TempAccessSummary::ACCESS_LOG_FILENAME . '.' . $date->format('Ymd');
        if (!file_exists($fileName)) {
            return null;
        }

        $csvReader = new CsvReader([
            'internalEncoding' => 'UTF-8',
            'fileEncoding' => 'UTF-8',
            'fileBom' => false,
            'filePath' => $fileName,
            'csvDelimiter' => "\t",
        ]);
        $csvReader->open();

        // アクセスログ取得
        $count = 0;
        while (true) {
            // 1行取得
            $line = $csvReader->read();
            if (!isset($line)) {
                break;
            }

            // 自分のドメイン以外、200ステータス以外は無視する
            if (
                Hash::get($line, (string)TempAccessSummary::ACCESS_LOG_CLIENT) !== Configure::readOrFail('Client.host')
                || Hash::get($line, (string)TempAccessSummary::ACCESS_LOG_STATUS) !== '200'
            ) {
                continue;
            }

            $pattern = '/^GET\\s' . preg_quote(TempAccessSummary::ACCESS_LOG_COUNT_PAGE, '/') . '/';
            if (preg_match($pattern, Hash::get($line, (string)TempAccessSummary::ACCESS_LOG_CALENDAR))) {
                $count++;
            }
        }

        $csvReader->close();

        return $count;
    }
}
