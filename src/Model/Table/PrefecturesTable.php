<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\AppTable;
use App\Model\Table\Traits\WordTrait;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Prefectures Model
 *
 * @method \App\Model\Entity\Prefecture newEmptyEntity()
 * @method \App\Model\Entity\Prefecture newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Prefecture[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Prefecture get($primaryKey, $options = [])
 * @method \App\Model\Entity\Prefecture findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Prefecture patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Prefecture[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Prefecture|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Prefecture saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Prefecture[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Prefecture[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Prefecture[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Prefecture[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class PrefecturesTable extends AppTable
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
        if (!$entity->isDirty('name')) {
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
            if (!$entity->isDirty('name')) {
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
            'code',
            'name',
        ]);
        $query->order([
            'Prefectures.sort_no' => 'ASC',
            'Prefectures.id' => 'ASC',
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
            'id',
            'code',
            'name',
            'default_name',
            'sort_no',
        ])->order([
            'sort_no' => 'ASC',
            'code' => 'ASC',
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
            }, 'prefectures');
        }

        return $this->cacheData;
    }

    /**
     * 都道府県の選択肢を取得
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
     * 都道府県の名称を取得
     *
     * @param int $prefectureId 都道府県ID
     * @return string 名称
     */
    public function getPrefectureName(int $prefectureId)
    {
        $valueOptions = $this->getValueOptions();

        return $valueOptions[$prefectureId];
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'prefectures');
        $this->cacheData = null;
    }
}
