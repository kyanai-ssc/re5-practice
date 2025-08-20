<?php
declare(strict_types=1);

namespace App\Model\InputType\Manager;

use App\Locale\Message;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItemDetail;
use App\Model\InputType\AbstractInputTypeManager;
use Cake\Datasource\EntityInterface;
use Cake\I18n\FrozenDate;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * DateSelect class.
 */
class DateSelect extends AbstractInputTypeManager
{
    /**
     * @var array
     */
    protected $canCreate = [
        FormGroup::FORM_TYPE_USER => true,
        FormGroup::FORM_TYPE_RESERVATION => true,
    ];

    /**
     * @var bool
     */
    protected $canSelectRequired = true;

    /**
     * @var bool
     */
    protected $canReservationDisplay = true;

    /**
     * @var bool
     */
    protected $hasFormItemDetails = true;

    /**
     * @var int
     */
    protected $formItemDetailNumber = 1;

    /**
     * @var array
     */
    protected $formItemDetailColumns = [
        'date_lower_limit',
        'date_upper_limit_type',
        'date_upper_limit_absolute',
        'date_upper_limit_relative',
        'date_default',
    ];

    /**
     * @var bool
     */
    protected $hasFormItemDetailTab = true;

    /**
     * @inheritDoc
     */
    public function validationFormItemDetail(Validator $validator)
    {
        $validator = parent::validationFormItemDetail($validator);

        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getTableLocator()->get('FormItemDetails');

        $validator
            ->requirePresence('date_lower_limit', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date_lower_limit', __(Message::ERROR_NOT_EMPTY), false)
            ->add('date_lower_limit', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $validator
            ->requirePresence('date_upper_limit_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('date_upper_limit_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('date_upper_limit_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($formItemDetailsTable->getFieldValueOptions('dateUpperLimitType'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $absoluteRequired = function ($context) {
            $dateUpperLimitType = Hash::get($context['data'], 'date_upper_limit_type');
            if (!$this->validateDateUpperLimitType($dateUpperLimitType)) {
                return false;
            }

            if (((string)$dateUpperLimitType) !== ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE)) {
                return false;
            }

            return true;
        };
        $relativeRequired = function ($context) {
            $dateUpperLimitType = Hash::get($context['data'], 'date_upper_limit_type');
            if (!$this->validateDateUpperLimitType($dateUpperLimitType)) {
                return false;
            }
            if (((string)$dateUpperLimitType) !== ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_RELATIVE)) {
                return false;
            }

            return true;
        };

        $validator
            ->requirePresence('date_upper_limit_absolute', function ($context) use ($absoluteRequired) {
                return $absoluteRequired($context);
            }, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate(
                'date_upper_limit_absolute',
                __(Message::ERROR_NOT_EMPTY),
                function ($context) use ($absoluteRequired) {
                    return !$absoluteRequired($context);
                }
            )
            ->add('date_upper_limit_absolute', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'date_lower_limit', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
            ]);

        $validator
            ->requirePresence('date_upper_limit_relative', function ($context) use ($relativeRequired) {
                return $relativeRequired($context);
            }, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString(
                'date_upper_limit_relative',
                __(Message::ERROR_NOT_EMPTY),
                function ($context) use ($relativeRequired) {
                    return !$relativeRequired($context);
                }
            )
            ->add('date_upper_limit_relative', [
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
                'compareGreaterOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MIN,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DIGIT, static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MIN),
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MAX,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MAX),
                ],
            ]);

        $validator
            ->requirePresence('date_default', false)
            ->allowEmptyDate('date_default')
            ->add('date_default', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFieldsLowerLimit' => [
                    'rule' => ['compareDateTimeFields', 'date_lower_limit', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                ],
                'compareFieldsUpperLimitAbsolute' => [
                    'rule' => ['compareDateTimeFields', 'date_upper_limit_absolute', '<='],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_TO_DATETIME),
                    'on' => function ($context) use (&$absoluteRequired) {
                        if (!call_user_func($absoluteRequired, $context)) {
                            return false;
                        }
                        $dateUpperLimitAbsolute = Hash::get($context['data'], 'date_upper_limit_absolute');
                        if (!$this->validateDateUpperLimitAbsolute($dateUpperLimitAbsolute)) {
                            return false;
                        }

                        return true;
                    },
                ],
                'compareFieldsUpperLimitRelative' => [
                    'rule' => function ($value, $context) {
                        $upperLimitRelative = $context['data']['date_upper_limit_relative'];
                        $upperLimitDate = new FrozenDate($this->commonData()->getNowDateTime()->format('Y-m-d'));
                        $upperLimitDate = $upperLimitDate->lastOfYear();
                        $upperLimitDate = $upperLimitDate->addYears((int)$upperLimitRelative);
                        if (!(new FrozenDate($value))->lessThanOrEquals($upperLimitDate)) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_TO_DATETIME),
                    'on' => function ($context) use (&$relativeRequired) {
                        if (!call_user_func($relativeRequired, $context)) {
                            return false;
                        }
                        $dateUpperLimitRelative = Hash::get($context['data'], 'date_upper_limit_relative');
                        if (!$this->validateDateUpperLimitRelative($dateUpperLimitRelative)) {
                            return false;
                        }

                        return true;
                    },
                ],
            ]);

        return $validator;
    }

    /**
     * 終了日タイプを検証
     *
     * @param mixed $dateUpperLimitType 終了日タイプ
     * @return bool 検証結果
     */
    protected function validateDateUpperLimitType($dateUpperLimitType)
    {
        /** @var \App\Model\Table\FormItemDetailsTable $formItemDetailsTable */
        $formItemDetailsTable = $this->getTableLocator()->get('FormItemDetails');

        $validator = new KuchenValidator();
        $validator
            ->requirePresence('date_upper_limit_type', true)
            ->allowEmptyString('date_upper_limit_type')
            ->add('date_upper_limit_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($formItemDetailsTable->getFieldValueOptions('dateUpperLimitType'))],
                    'last' => true,
                ],
            ]);

        $errors = $validator->validate(['date_upper_limit_type' => $dateUpperLimitType]);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * 年月日指定の終了日を検証
     *
     * @param mixed $dateUpperLimitAbsolute 終了日タイプ
     * @return bool 検証結果
     */
    protected function validateDateUpperLimitAbsolute($dateUpperLimitAbsolute)
    {
        $validator = new KuchenValidator();
        $validator
            ->requirePresence('date_upper_limit_absolute', true)
            ->allowEmptyDate('date_upper_limit_absolute')
            ->add('date_upper_limit_absolute', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                ],
            ]);

        $errors = $validator->validate(['date_upper_limit_absolute' => $dateUpperLimitAbsolute]);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * 年数指定の終了日を検証
     *
     * @param mixed $dateUpperLimitRelative 終了日タイプ
     * @return bool 検証結果
     */
    protected function validateDateUpperLimitRelative($dateUpperLimitRelative)
    {
        $validator = new KuchenValidator();
        $validator
            ->requirePresence('date_upper_limit_relative', true)
            ->allowEmptyString('date_upper_limit_relative')
            ->add('date_upper_limit_relative', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber', true],
                    'last' => true,
                ],
                'compareGreaterOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_GREATER_OR_EQUAL,
                        static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MIN,
                    ],
                    'last' => true,
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MAX,
                    ],
                    'last' => true,
                ],
            ]);

        $errors = $validator->validate(['date_upper_limit_relative' => $dateUpperLimitRelative]);
        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * タイプによるエンティティの整形
     *
     * @param \Cake\Datasource\EntityInterface $entity entity
     * @return void
     */
    public function filterType(EntityInterface $entity)
    {
        $details = $entity->get('form_item_details');

        foreach ($details as $index => $detail) {
            $type = $detail->get('date_upper_limit_type');
            if ($type === FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE) {
                $details[$index]->set('date_upper_limit_relative', null);
            } else {
                $details[$index]->set('date_upper_limit_absolute', null);
            }
        }
        $entity->set('form_item_details', $details);
    }
}
