<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * EventSmartLocksTable Model
 *
 * @method \App\Model\Entity\EventSmartLock newEmptyEntity()
 * @method \App\Model\Entity\EventSmartLock newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventSmartLock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventSmartLock get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventSmartLock findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\EventSmartLock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventSmartLock[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventSmartLock|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventSmartLock saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventSmartLock[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventSmartLock[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventSmartLock[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\EventSmartLock[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EventSmartLocksTable extends AppTable
{
    public const SMART_LOCK_DEVICE_KEY_MAX = 100;

    public const CSV_COLUMN_SMART_LOCK_DEVICE_KEY = 'smart_lock_device_key';
    public const CSV_COLUMN_SMART_LOCK_KEY_URL_FLG = 'smart_lock_key_url_flg';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->hasOne('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!($validator instanceof KuchenValidator)) {
            throw new CakeException();
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if ($systemSettingsTable->getData()->useSmartLock()) {
            $validator
                ->requirePresence('smart_lock_device_key', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('smart_lock_device_key')
                ->add('smart_lock_device_key', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::SMART_LOCK_DEVICE_KEY_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::SMART_LOCK_DEVICE_KEY_MAX),
                    ],
                ]);

            $smartLock = new SmartLockLinkage();
            if ($smartLock->useAkerun()) {
                $validator
                    ->requirePresence('smart_lock_key_url_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
                    ->allowEmptyString('smart_lock_key_url_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
                    ->add('smart_lock_key_url_flg', [
                        'isScalar' => [
                            'rule' => ['isScalar'],
                            'last' => true,
                            'message' => __(Message::ERROR_INVALID_VALUE),
                        ],
                        'inList' => [
                            'rule' => ['inList', array_keys(Configure::readOrFail('Master.event.common'))],
                            'last' => true,
                            'message' => __(Message::ERROR_IN_LIST),
                        ],
                    ]);
            }
        }

        return $validator;
    }

    /**
     * CSVへ出力するデータを生成
     *
     * @param array|null $csvItem 出力項目
     * @param array $options オプション
     * @return string CSV1セルの情報
     */
    public function generateCsvData($csvItem, array $options = [])
    {
        $csvData = [];

        if (empty($csvItem)) {
            return '';
        }

        $asHeader = $options['asHeader'];

        $multipleData = [];
        foreach ($asHeader as $column) {
            $multipleData[] = $this->formatCsvData($column, $options + ['smart_lock' => $csvItem]);
        }
        $csvData = $this->csvFormat()->csvForMultiple($multipleData);

        return $csvData;
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
        /** @var \App\Model\Entity\EventSmartLock $eventSmartLock */
        $eventSmartLock = $options['smart_lock'];
        $data = $eventSmartLock->get($column);
        $value = '';
        if (!is_null($data)) {
            switch ($column) {
                case static::CSV_COLUMN_SMART_LOCK_KEY_URL_FLG:
                    $value = $this->csvFormat()->csvForId($data, Configure::readOrFail('Master.event.common')[$data]);
                    break;
                case static::CSV_COLUMN_SMART_LOCK_DEVICE_KEY:
                default:
                    $value = $data;
                    break;
            }
        }

        return $value;
    }
}
