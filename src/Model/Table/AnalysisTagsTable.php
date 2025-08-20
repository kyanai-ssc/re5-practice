<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\SystemSetting;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * AnalysisTags Model
 *
 * @method \App\Model\Entity\AnalysisTag newEmptyEntity()
 * @method \App\Model\Entity\AnalysisTag newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AnalysisTag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AnalysisTag get($primaryKey, $options = [])
 * @method \App\Model\Entity\AnalysisTag findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AnalysisTag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AnalysisTag[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AnalysisTag|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AnalysisTag saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AnalysisTag[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AnalysisTag[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AnalysisTag[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AnalysisTag[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AnalysisTagsTable extends AppTable
{
    public const CONTENTS_MAX = 100000;

    /**
     * @var array|string|null
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
            'afterSave' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'analysisTagIndex' => Configure::readOrFail('Master.analysisTag.typeIndex'),
            'analysisTagName' => Configure::readOrFail('Master.analysisTag.typeName'),
        ];

        return $fieldValueOptions;
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
        if (!$entity->isDirty('contents')) {
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
            if ($entity->isDirty('contents')) {
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
        $validator->requirePresence('contents', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('contents')
            ->add('contents', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CONTENTS_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CONTENTS_MAX),
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
            'type',
            'contents',
        ]);
        $query->enableHydration(false);
        $query->formatResults(function ($result) {
            return $result->combine('type', 'contents');
        });

        return $query;
    }

    /**
     * 全entityの取得
     *
     * @param \Cake\ORM\Query $query query
     * @return \Cake\ORM\Query query
     */
    public function findAnalysisTags(Query $query)
    {
        return $query->find('all')->select(['id', 'type', 'contents'])->orderAsc('id');
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
     * @param int|null $type タイプ
     * @return array|string|null データ
     */
    public function getData(?int $type = null)
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->toArray();
            }, 'analysisTags');
        }

        if (((string)$type) !== '') {
            return Hash::get($this->cacheData, (string)$type);
        }

        return $this->cacheData;
    }

    /**
     * プランの権限をチェック
     *
     * @return void
     */
    public function checkPlan()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        $systemSetting = $systemSettingsTable->getData();
        if ($systemSetting->get('contract_plan') === SystemSetting::CONTRACT_PLAN_LITE) {
            throw new BadRequestException(Message::ERROR_PLAN_AUTHORITY);
        }
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'analysisTags');
        $this->cacheData = null;
    }
}
