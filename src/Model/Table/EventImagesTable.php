<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use Cake\Validation\Validator;

/**
 * EventImages Model
 *
 * @method \App\Model\Entity\EventImage newEmptyEntity()
 * @method \App\Model\Entity\EventImage newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventImage[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventImage get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventImage findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventImage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventImage[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventImage|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventImage saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventImage[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventImage[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventImage[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventImage[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventImagesTable extends AppTable
{
    public const URL_MAX = 1000;

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
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('url', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('url')
            ->add('url', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::URL_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::URL_MAX),
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
            $data[] = $csv->get('url');
        }

        return $this->csvFormat()->csvForHasMany($data);
    }
}
