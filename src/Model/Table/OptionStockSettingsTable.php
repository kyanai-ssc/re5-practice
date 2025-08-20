<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Option;
use App\Validation\CustomValidation;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * OptionStockSettings Model
 *
 * @method \App\Model\Entity\OptionStockSetting newEmptyEntity()
 * @method \App\Model\Entity\OptionStockSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\OptionStockSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OptionStockSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\OptionStockSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\OptionStockSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OptionStockSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OptionStockSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OptionStockSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OptionStockSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptionStockSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptionStockSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\OptionStockSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class OptionStockSettingsTable extends AppTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Options', [
            'foreignKey' => 'option_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('stock', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('stock', __(Message::ERROR_NOT_EMPTY), false)
            ->add('stock', [
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
                'lessThanOrEqual' => [
                    'rule' => ['comparison', Validation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator->requirePresence('usage_timestamp_from', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('usage_timestamp_from', __(Message::ERROR_NOT_EMPTY), false)
            ->add('usage_timestamp_from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
            ]);

        $validator->requirePresence('usage_timestamp_to', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDateTime('usage_timestamp_to', __(Message::ERROR_NOT_EMPTY), false)
            ->add('usage_timestamp_to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'datetime' => [
                    'rule' => [
                        'datetime',
                        'ymd',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE_TIME),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'usage_timestamp_from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function ($context) use ($validator) {
                        if (!($validator instanceof KuchenValidator)) {
                            throw new CakeException();
                        }

                        if (
                            $validator->isValid('usage_timestamp_from')
                            && Validation::notBlank(Hash::get($context['data'], 'usage_timestamp_from'))
                        ) {
                            return true;
                        }

                        return false;
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * 削除可能かどうか
     *
     * @param \App\Model\Entity\Option $option オプション
     * @param array $originalStockSettings 変更前データ
     * @param array $deleteIds 削除ID
     * @return bool
     */
    public function canDelete(Option $option, array $originalStockSettings, array $deleteIds)
    {
        /** @var \App\Model\Table\OptionsTable $optionsTable */
        $optionsTable = $this->getAssociation('Options')->getTarget();

        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                if (
                    !$optionsTable->checkStockBetweenDate(
                        $option,
                        $originalStockSettings[$deleteId]['usage_timestamp_from'],
                        $originalStockSettings[$deleteId]['usage_timestamp_to'],
                        true
                    )
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
