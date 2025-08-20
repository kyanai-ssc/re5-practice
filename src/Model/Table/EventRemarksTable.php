<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\ArrayUtility;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Validation\Validator;

/**
 * EventRemarks Model
 *
 * @method \App\Model\Entity\EventRemark newEmptyEntity()
 * @method \App\Model\Entity\EventRemark newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventRemark[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventRemark get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventRemark findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventRemark patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventRemark[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventRemark|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventRemark saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventRemark[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventRemark[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventRemark[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventRemark[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventRemarksTable extends AppTable
{
    public const NAME_MAX = 100;
    public const REMARK_MAX = 10000;

    public const CSV_COLUMN_NAME = 'name';
    public const CSV_COLUMN_DETAIL_DISPLAY_FLG = 'detail_display_flg';
    public const CSV_COLUMN_REMARK = 'remark';
    public const CSV_COLUMN_FORM_ITEM_ID = 'form_item_id';

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('detail_display_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('detail_display_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('detail_display_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('remarkDetailDisplayFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

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
            ->requirePresence('remark', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('remark', __(Message::ERROR_NOT_EMPTY), false)
            ->add('remark', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::REMARK_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::REMARK_MAX),
                ],
            ]);

        $validator
            ->requirePresence('form_item_id', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('form_item_id', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('form_item_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('formItemId')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['name']) && is_string($data['name'])) {
            $data->offsetSet('name', $this->csvFormat()->replaceSeparetorForMultiple($data['name']));
        }
        if (isset($data['remark']) && is_string($data['remark'])) {
            $data->offsetSet('remark', $this->csvFormat()->replaceSeparetorForMultiple($data['remark']));
        }
    }

    /**
     * 注釈項目の重複チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return bool 結果
     */
    public function checkDuplicationFormId(EntityInterface $entity)
    {
        $eventRemarks = $entity->get('event_remarks');

        $formItems = [];
        $success = true;
        foreach ($eventRemarks as $eventRemark) {
            if (ArrayUtility::arraySearch($eventRemark->get('form_item_id'), $formItems) !== false) {
                $eventRemark->setError('form_item_id', ['_duplication' => __(Message::ERROR_DUPLICATION)]);
                $success = false;
            }
            $formItems[] = $eventRemark->get('form_item_id');
        }

        return $success;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param array|null $csvItems 出力項目
     * @param array $options オプション
     * @return string CSV1セルの情報
     */
    public function generateCsvData($csvItems, array $options = [])
    {
        $csvData = [];

        if (empty($csvItems)) {
            return '';
        }

        $asHeader = $options['asHeader'];

        foreach ($csvItems as $csv) {
            $multipleData = [];
            foreach ($asHeader as $column) {
                $multipleData[] = $this->formatCsvData($column, $options + ['remarks' => $csv]);
            }
            $csvData[] = $this->csvFormat()->csvForMultiple($multipleData, true);
        }

        return $this->csvFormat()->csvForHasMany($csvData);
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param string $column カラム
     * @param array $options オプション
     * @return string データ
     */
    public function formatCsvData(string $column, array $options)
    {
        /** @var \App\Model\Entity\EventRemark $eventRemarks */
        $eventRemarks = $options['remarks'];
        $valueOptions = $this->getFieldValueOptions();
        $data = $eventRemarks->get($column);
        $value = '';
        if (!is_null($data)) {
            switch ($column) {
                case static::CSV_COLUMN_DETAIL_DISPLAY_FLG:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['common'][$data]);
                    break;
                case static::CSV_COLUMN_FORM_ITEM_ID:
                    $value = $this->csvFormat()->csvForId($data, $valueOptions['formItemId'][$data]);
                    break;
                default:
                    $value = $data;
                    break;
            }
        }

        return $value;
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array $excludeFormItemId 除外するフォーム項目ID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemId = null)
    {
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $where = [
            'EventRemarks.form_item_id IN' => $formItemsQuery,
        ];
        if (!empty($excludeFormItemId)) {
            $where['EventRemarks.form_item_id NOT IN'] = (array)$excludeFormItemId;
        }
        $this->deleteAll($where);
    }
}
