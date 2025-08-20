<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Word;
use App\Model\Table\Traits\WordTrait;
use App\Validation\CustomValidation;
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
 * Words Model
 *
 * @method \App\Model\Entity\Word newEmptyEntity()
 * @method \App\Model\Entity\Word newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Word[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Word get($primaryKey, $options = [])
 * @method \App\Model\Entity\Word findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Word patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Word[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Word|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Word saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Word[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Word[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Word[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Word[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class WordsTable extends AppTable
{
    use WordTrait;

    public const WORD_MAX = 1000;

    /**
     * @var array|null
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
        if (!$entity->isDirty('word')) {
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

        if ($this->checkEntityErrors($entities)) {
            return false;
        }

        foreach ($entities as $entity) {
            if ($entity->isDirty('word')) {
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
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->getWordValidator($validator, 'word', static::WORD_MAX);
        $validator->add('word', [
            'ngWord' => [
                'rule' => function ($value) {
                    return !CustomValidation::includeString($value, Configure::readOrFail('Setting.word.ngWords'), [
                        'case' => false,
                        'width' => false,
                        'kana' => false,
                    ]);
                },
                'last' => true,
                'message' => __(Message::ERROR_NG_WORD),
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
            'translate_key',
            'word',
        ]);

        $adminFlg = Hash::get($options, 'inputs.admin_flg', false);
        if (!$adminFlg) {
            $query->where([
                'Words.admin_flg' => Word::ADMIN_FLG_OFF,
            ]);
        }

        $query->order([
            'Words.id' => 'ASC',
        ]);
        $query->enableHydration(false);

        $query->formatResults(function ($result) {
            return $result->combine('translate_key', 'word');
        });

        return $query;
    }

    /**
     * 文言取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findWord(Query $query, array $options)
    {
        $query->select([
            'Words.id',
            'Words.name',
            'Words.type',
            'Words.word_default',
            'Words.word',
            'Words.code',
            'Words.category',
            'Words.function',
            'Words.multiple_row_flg',
        ])
            ->where(
                [
                    'Words.type' => $options['word_type'],
                    'Words.admin_flg' => Word::ADMIN_FLG_OFF,
                ]
            )
            ->order([
                'Words.category' => 'ASC',
                'Words.function' => 'ASC',
                'Words.code' => 'ASC',
                'Words.id' => 'ASC',
            ]);

        return $query;
    }

    /**
     * サイト名更新用ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUpdateSiteName(Query $query, array $options)
    {
        $query->select([
            'id',
            'word',
        ]);
        $query->where([
            'Words.translate_key' => 'COMMON_TITLE',
        ]);

        return $query;
    }

    /**
     * データを取得
     *
     * @param bool $adminFlg 管理側フラグ
     * @return array データ
     */
    public function getData($adminFlg = false)
    {
        $key = 'user';
        if ($adminFlg) {
            $key = 'admin';
        }

        if (!isset($this->cacheData[$key])) {
            $this->cacheData[$key] = Cache::remember($key, function () use ($adminFlg) {
                $query = $this->find('default', [
                    'inputs' => [
                        'admin_flg' => $adminFlg,
                    ],
                ]);

                return $query->toArray();
            }, 'words');
        }

        return $this->cacheData[$key];
    }

    /**
     * 通常文言の一覧を取得
     *
     * @return array
     */
    public function getEntitiesWord()
    {
        return $this->find('word', ['word_type' => Word::TYPE_WORD])->toArray();
    }

    /**
     * エラー文言の一覧を取得
     *
     * @return array
     */
    public function getEntitiesError()
    {
        return $this->find('word', ['word_type' => Word::TYPE_ERROR])->toArray();
    }

    /**
     * キャッシュファイル削除
     *
     * @param bool $includeAdmin 管理側フラグ
     * @return void
     */
    public function deleteCacheData(bool $includeAdmin = true)
    {
        Cache::delete('user', 'words');
        if ($includeAdmin) {
            Cache::delete('admin', 'words');
        }
        $this->cacheData = null;
    }

    /**
     * サイト名を更新
     *
     * @param string $siteName サイト名
     * @return void
     */
    public function updateSiteName(string $siteName)
    {
        $entity = $this->find('updateSiteName')->first();
        if (!($entity instanceof EntityInterface)) {
            throw new CakeException();
        }

        $entity->set('word', $siteName);
        $this->saveOrFail($entity);
    }
}
