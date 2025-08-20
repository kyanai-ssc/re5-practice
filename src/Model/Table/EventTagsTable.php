<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Validation\CustomValidation;
use Cake\Validation\Validator;

/**
 * EventTags Model
 *
 * @method \App\Model\Entity\EventTag newEmptyEntity()
 * @method \App\Model\Entity\EventTag newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventTag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventTag get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventTag findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventTag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventTag[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventTag|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventTag saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventTag[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventTag[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventTag[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventTag[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventTagsTable extends AppTable
{
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
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->allowEmptyString('tag_id')
            ->add('tag_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'exists' => [
                    'rule' => function ($check) {
                        if (!empty($check)) {
                            return $this->getTableLocator()->get('Tags')->exists([
                                'id' => $check,
                            ]);
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],

            ]);

        return $validator;
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
        $data = [];

        if (empty($csvItems)) {
            return '';
        }

        foreach ($csvItems as $csv) {
            $data[] = $this->csvFormat()->csvForId($csv->get('tag_id'), $csv->get('tag')->get('name'));
        }

        return $this->csvFormat()->csvForHasMany($data);
    }
}
