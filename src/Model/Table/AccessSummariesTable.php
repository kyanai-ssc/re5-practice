<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Entity\User;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;

/**
 * AccessSummaries Model
 *
 * @method \App\Model\Entity\AccessSummary newEmptyEntity()
 * @method \App\Model\Entity\AccessSummary newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AccessSummary[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AccessSummary get($primaryKey, $options = [])
 * @method \App\Model\Entity\AccessSummary findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AccessSummary patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AccessSummary[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AccessSummary|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessSummary saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AccessSummary[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AccessSummary[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AccessSummary[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AccessSummary[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AccessSummariesTable extends AppTable
{
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
        $now = $this->commonData()->getNowDateTime();
        $yesterday = $now->subDays(1);

        $query->select([
            'users',
            'reservations',
            'calendar',
        ]);
        $query->where([
            'access_date' => $yesterday->format('Y-m-d'),
        ]);

        return $query;
    }

    /**
     * 集計Entityの作成
     *
     * @param string|\DateTimeInterface $accessDate 日付
     * @return \App\Model\Entity\AccessSummary
     */
    public function createEntity($accessDate)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');
        /** @var \App\Model\Table\TempAccessSummariesTable $tempAccessSummariesTable */
        $tempAccessSummariesTable = $this->getTableLocator()->get('TempAccessSummaries');

        $accessDate = DateTimeUtility::convertToDateObject($accessDate);
        if (!isset($accessDate)) {
            throw new CakeException();
        }

        $access = 0;
        $tempAccessSummary = $tempAccessSummariesTable->find('access', [
            'inputs' => [
                'access_date' => $accessDate,
            ],
        ])->first();
        if (isset($tempAccessSummary['calendar'])) {
            $access = $tempAccessSummary['calendar'];
        }

        // DBから会員登録数と予約登録数を取得
        $created = $this->driverExpression()->dateFormat('created', 'ymd', '-');
        $usersQuery = $usersTable->find()->where(function (QueryExpression $exp) use ($created, $accessDate) {
            $exp->eq($created, $accessDate->format('Y-m-d'));

            return $exp;
        });
        $usersQuery->where(['guest_flg' => User::GUEST_FLG_OFF, 'withdrawal_flg' => User::WITHDRAWAL_FLG_OFF]);
        $usersCount = $usersQuery->count();

        $reservationsCount = $reservationsTable->find()->where(
            function (QueryExpression $exp) use ($created, $accessDate) {
                $exp->eq($created, $accessDate->format('Y-m-d'));

                return $exp;
            }
        )->count();

        $entity = $this->newEntity([
            'access_date' => new FrozenDate($accessDate->format('Y-m-d')),
            'users' => $usersCount,
            'reservations' => $reservationsCount,
            'calendar' => $access,
        ], ['validate' => false]);

        return $entity;
    }
}
