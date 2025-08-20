<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\SmartLock;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * SmartLocks Model
 *
 * @method \App\Model\Entity\SmartLock newEmptyEntity()
 * @method \App\Model\Entity\SmartLock newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SmartLock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SmartLock get($primaryKey, $options = [])
 * @method \App\Model\Entity\SmartLock findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SmartLock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SmartLock[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SmartLock|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SmartLock saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SmartLock[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SmartLock[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SmartLock[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SmartLock[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SmartLocksTable extends AppTable
{
    /**
     * ID：固定となる主キーの値
     */
    public const BASE_ID = 1;

    /**
     * @var \App\Model\Entity\SmartLock|null
     */
    protected $cacheData = null;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
        ]);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * Model.beforeFind イベントで実行されるメソッド
     * Client Secret を取得している場合、その復号化を行う
     * また不要な登録処理を避けるため、setDirty() で未変更扱いとする
     *
     * @param \Cake\Event\Event $event イベント
     * @param \Cake\ORM\Query $query クエリ
     * @param \ArrayObject $options オプション
     * @param bool $primary プライマリー
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options, bool $primary): void
    {
        $query->formatResults(function (CollectionInterface $results) {
            return $results->map(function (SmartLock $smartLock) {
                $clientSecret = $smartLock->get('client_secret');
                if (is_string($clientSecret) && $clientSecret !== '') {
                    $smartLock->set('client_secret', $smartLock->decryptClientSecret($clientSecret));
                    // 複合化は変更と扱わないものとする
                    $smartLock->setDirty('client_secret', false);
                }

                return $smartLock;
            });
        });
    }

    /**
     * Model.beforeSave イベントで実行されるメソッド
     * Client Secret が変更されている場合、その暗号化を行う
     *
     * @param \Cake\Event\Event $event イベント
     * @param \App\Model\Entity\SmartLock $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        $clientSecret = $entity->get('client_secret');
        // Client Secret に値が存在し、変更のあった場合のみ暗号化する
        if (is_string($clientSecret) && $clientSecret !== '' && $entity->isDirty('client_secret')) {
            $entity->set('client_secret', $entity->encryptClientSecret($clientSecret));
        }
    }

    /**
     * Model.afterSave イベントで実行されるメソッド
     * キャッシュの削除を行う
     *
     * @param \Cake\Event\Event $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        $this->deleteCacheData();
    }

    /**
     * キャッシュ削除
     *
     * @return void
     */
    public function deleteCacheData(): void
    {
        Cache::delete('data', 'smartLocks');
        $this->cacheData = null;
    }

    /**
     * キャッシュ経由でデータを取得する
     *
     * @return \App\Model\Entity\SmartLock
     */
    public function getData(): SmartLock
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->firstOrFail();
            }, 'smartLocks');
        }

        return $this->cacheData;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'formItemsForAkerun' => $this->getFormItemsForAkerun(),
            'appUseFlg' => Configure::readOrFail('Master.akerun.appUseFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('form_item_id', false)
            ->allowEmptyString('form_item_id')
            ->add('form_item_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('formItemsForAkerun')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);
        $validator
            ->requirePresence('app_use_flg', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('app_use_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('app_use_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('appUseFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['form_item_id'], 'FormItems'));

        return $rules;
    }

    /**
     * Akerunユーザー名連携する会員項目一覧を取得
     *
     * @return array  Akerunユーザー名連携する会員一覧
     */
    public function getFormItemsForAkerun()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');
        $formItems = $formItemsTable->find('formItemsForAkerun')->toArray();

        return Hash::combine($formItems, '{n}.id', '{n}.name');
    }

    /**
     * 固定IDを用いて単一のレコードを取得するファインダー
     * スマートロック関連のコマンドで使用する
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefault(Query $query, array $options): Query
    {
        return $query->where(['id' => static::BASE_ID]);
    }

    /**
     * ファインダーで取得できるEntityがあれば更新、なければ登録のためのentityを取得する
     *
     * @param array $data 登録値
     * @param string $finder ファインダー
     * @return \App\Model\Entity\SmartLock
     */
    public function getUpsertEntity(array $data, string $finder = 'default'): SmartLock
    {
        /** @var \App\Model\Entity\SmartLock $smartLock */
        $smartLock = $this->find($finder)->first() ?: $this->newEntity([], ['validate' => false]);

        return $this->patchEntity($smartLock, $data, ['validate' => false]);
    }

    /**
     * スマートロック連携用のロックを取得
     *
     * @param int $code ロック時のコード (テーブルの PK 値)
     * @return void
     */
    public function getLockForSmartLock(int $code): void
    {
        $this->getLock(static::LOCK_TYPE_SMART_LOCK, $code);
    }

    /**
     * スマートロック連携用のロックを解放
     *
     * @param int $code ロック時のコード (テーブルの PK 値)
     * @return void
     */
    public function releaseLockForSmartLock(int $code): void
    {
        $this->releaseLock(static::LOCK_TYPE_SMART_LOCK, $code);
    }
}
