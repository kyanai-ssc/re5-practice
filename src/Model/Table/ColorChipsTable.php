<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\ColorChip;
use App\Utility\ArrayUtility;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * ColorChips Model
 *
 * @method \App\Model\Entity\ColorChip newEmptyEntity()
 * @method \App\Model\Entity\ColorChip newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ColorChip[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ColorChip get($primaryKey, $options = [])
 * @method \App\Model\Entity\ColorChip findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ColorChip patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ColorChip[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ColorChip|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ColorChip saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ColorChip[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ColorChip[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ColorChip[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ColorChip[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ColorChipsTable extends AppTable
{
    public const NAME_MAX = 100;
    public const COLOR_CODE_MAX = 7;

    /**
     * @var array|null
     */
    protected $cacheData = null;

    /**
     * @var array|null
     */
    protected $colorChipsByType = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasMany('Events', [
            'foreignKey' => 'color_chip_id',
        ]);

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
            'frontDisplayFlg' => Configure::readOrFail('Master.colorChip.frontDisplayFlg'),
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
        if (
            !$entity->isDirty('sort_no')
            && !$entity->isDirty('name')
            && !$entity->isDirty('color_code')
            && !$entity->isDirty('front_display_flg')
        ) {
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
        $colorChips = $this->find('colorChip');
        $defaultData = [];
        //削除不可データが削除されていればエラーとする
        foreach ($colorChips as $colorChip) {
            if (
                !$colorChip->canDelete()
                && ArrayUtility::arraySearch($colorChip->get('id'), array_column($entities, 'id')) === false
            ) {
                return false;
            }
            if ($colorChip->get('default_flg') === ColorChip::DEFAULT_FLG_ON) {
                $defaultData[] = $colorChip;
            }
        }

        $sortNo = 1;
        /** @var \Cake\ORM\Entity $entity */
        foreach ($entities as $entity) {
            if ($entity->get('type') === null) {
                $entity->set('type', ColorChip::TYPE_ADD);
            }

            if ($entity->get('sort_no') !== $sortNo) {
                $entity->set('sort_no', $sortNo);
            }
            $sortNo++;
        }

        foreach ($defaultData as $default) {
            if (ArrayUtility::arraySearch($default->get('id'), array_column($entities, 'id')) === false) {
                return false;
            }
        }

        if ($this->checkEntityErrors($entities)) {
            return false;
        }

        //変更があるデータを操作ログに登録
        foreach ($entities as $entity) {
            if (
                $entity->isDirty('sort_no')
                || $entity->isDirty('name')
                || $entity->isDirty('color_code')
                || $entity->isDirty('front_display_flg')
            ) {
                $operationLogsId = Hash::get($options, 'operationLogsId', []);
                $operationLogsId[$entity->get('id')] = $entity;

                $options->offsetSet('operationLogsId', $operationLogsId);
            }
        }

        $condition = [
            [$this->excludeQueryByEntities($entities)],
            [
                'default_flg' => ColorChip::DEFAULT_FLG_OFF,
                'type' => ColorChip::TYPE_ADD,
            ],
        ];
        $this->deleteAll($condition);

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
            'type',
            'name',
            'color_code',
            'front_display_flg',
            'default_flg',
            'sort_no',
        ]);
        $query->order([
            'ColorChips.sort_no' => 'ASC',
            'ColorChips.id' => 'ASC',
        ]);
        $query->enableHydration(false);

        $query->formatResults(function ($colorChips) {
            $result = [];
            foreach ($colorChips as $colorChip) {
                $result[$colorChip['id']] = $colorChip;
            }

            return $result;
        });

        return $query;
    }

    /**
     * 取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findColorChip(Query $query, array $options)
    {
        $query->select([
            'ColorChips.id',
            'ColorChips.type',
            'ColorChips.name',
            'ColorChips.sort_no',
            'ColorChips.default_flg',
            'ColorChips.color_code',
            'ColorChips.front_display_flg',
        ])->contain([
            'Events' => function (Query $q) {
                $q->select([
                    'color_chip_id',
                    'count' => $q->func()->count('color_chip_id'),
                ])
                    ->group(['color_chip_id'])
                    ->order('color_chip_id', true);

                return $q;
            },
        ]);

        $query->order([
            'ColorChips.sort_no' => 'ASC',
            'ColorChips.type' => 'ASC',
            'ColorChips.id' => 'ASC',
        ]);

        if (!empty($options['where'])) {
            $query->where($options['where']);
        }

        return $query;
    }

    /**
     * カラーチップ一覧を取得[id:name]
     *
     * @param bool $default デフォルト
     * @param bool $includeAdd 追加分を含む
     * @param array $exclodeTypes 取得しないタイプを設定
     * @return array
     */
    public function getColorChipList(bool $default = false, bool $includeAdd = false, $exclodeTypes = [])
    {
        $query = $this->find('list')->select(['id', 'name']);

        if ($default && !$includeAdd) {
            $query->where(['default_flg' => ColorChip::DEFAULT_FLG_ON]);
        }

        if (!$default && $includeAdd) {
            $query->where(['default_flg' => ColorChip::DEFAULT_FLG_OFF]);
        }

        if (!empty($exclodeTypes)) {
            $query->where(['type NOT IN' => $exclodeTypes]);
        }

        return $query->toArray();
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
            ->add('name', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::NAME_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                ],
            ]);

        $validator
            ->requirePresence('color_code', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('color_code', __(Message::ERROR_NOT_EMPTY), false)
            ->add('color_code', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::COLOR_CODE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::COLOR_CODE_MAX),
                ],
                'hexColor' => [
                    'rule' => [
                        'hexColor',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_COLOR_CODE),
                ],
            ]);

        $validator
            ->requirePresence('front_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('front_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('front_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('frontDisplayFlg'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * afterSaveManyCommit hook
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param array $entities entities
     * @param \ArrayObject $options オプション
     * @return bool
     */
    public function afterSaveManyCommit(EventInterface $event, array $entities, ArrayObject $options)
    {
        $this->deleteCacheData();

        return true;
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
            }, 'colorChips');
        }

        return $this->cacheData;
    }

    /**
     * IDからデータを取得
     *
     * @param int $id ID
     * @return array データ
     */
    public function getDataById($id)
    {
        $data = $this->getData();

        return $data[$id];
    }

    /**
     * タイプからデータを取得
     *
     * @param int $type タイプ
     * @return array データ
     */
    public function getDataByDefaultType($type)
    {
        if (!isset($this->colorChipsByType)) {
            $colorChipsByType = [];
            foreach ($this->getData() as $data) {
                if (((string)$data['default_flg']) === ((string)ColorChip::DEFAULT_FLG_ON)) {
                    $colorChipsByType[$data['type']] = $data;
                }
            }

            $this->colorChipsByType = $colorChipsByType;
        }

        return $this->colorChipsByType[$type];
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'colorChips');
        $this->cacheData = null;
    }
}
