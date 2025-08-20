<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Validation\Validator;

/**
 * EventStockMarks Model
 *
 * @method \App\Model\Entity\EventStockMark newEmptyEntity()
 * @method \App\Model\Entity\EventStockMark newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockMark[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventStockMark get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventStockMark findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventStockMark patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockMark[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventStockMark|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockMark saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventStockMark[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockMark[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockMark[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventStockMark[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventStockMarksTable extends AppTable
{
    public const CSV_COLUMN_SYMBOLIC = 'symbolic';
    public const CSV_COLUMN_NUMBER = 'number';

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
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'symbolicDisp' => Configure::readOrFail('Master.event.symbolicDisp'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('number', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('number', __(Message::ERROR_NOT_EMPTY), false)
            ->add('number', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        $validator
            ->requirePresence('symbolic', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('symbolic', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('symbolic', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('symbolicDisp'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * 在庫数の重複チェック
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @return bool
     */
    public function checkDuplicationNumber($entity)
    {
        $numbers = [];
        $success = true;
        foreach ($entity->get('event_stock_marks') as $eventStockMark) {
            if (ArrayUtility::arraySearch($eventStockMark->get('number'), $numbers) !== false) {
                $eventStockMark->setError('number', ['_duplication' => __(Message::ERROR_DUPLICATION)]);
                $success = false;
            }
            $numbers[] = $eventStockMark->get('number');
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

        $valueOptions = $this->getFieldValueOptions();
        foreach ($csvItems as $csv) {
            $stockMarks['symbolic'] = $this->csvFormat()->csvForId(
                $csv->get('symbolic'),
                $valueOptions['symbolicDisp'][$csv->get('symbolic')]
            );
            $stockMarks['number'] = $csv->get('number');
            $csvData[] = $this->csvFormat()->csvForMultiple($stockMarks);
        }

        return $this->csvFormat()->csvForHasMany($csvData);
    }
}
