<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\Reservation;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\AdditionTypeInterface;
use App\Model\InputType\Item\Type\AdditionTypeTrait;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use App\Model\InputType\Item\Type\ChargeTypeInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\OptionTypeInterface;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * ReservationOption class.
 */
class ReservationOption extends AbstractInputTypeItem implements
    AdditionTypeInterface,
    CalendarOutputInterface,
    ChargeTypeInterface,
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
    MailOutputInterface,
    OptionTypeInterface,
    SearchDisplayInterface
{
    use AdditionTypeTrait;
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    /**
     * @var string
     */
    protected $tableName = 'reservation_options';

    /**
     * @var array|null
     */
    protected $formItemOptions = null;

    /**
     * @var array|null
     */
    protected $optionValueOptions = null;

    /**
     * @var array|null
     */
    protected $numberValueOptions = null;

    /**
     * @var array|null
     */
    protected $searchValueOptions = null;

    /**
     * @var string
     */
    protected $delimiter = "\n";

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        $optionValueOptions = $this->getOptionValueOptions();
        if (empty($optionValueOptions)) {
            $this->displayType['canDisplay'] = false;
            $this->displayType['canInput'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
        $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');

        $data = [];
        if (is_null($options)) {
            return null;
        }

        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        $reservationOptions = $reservation->get('reservation_options');
        if (!isset($reservationOptions)) {
            return null;
        }

        $formItemOptions = $this->getFormItemOptions();
        $event = Hash::get((array)$options, 'event');
        $mode = Hash::get((array)$options, 'mode');

        $onlyUsedOption = false;
        if (
            !$this->commonData()->existsAdminLoginData()
            && $mode === 'edit'
        ) {
            $onlyUsedOption = true;
        }

        foreach ($reservationOptions as $reservationOption) {
            // この項目に対して表示パターン設定でチェックがつけられていないオプションの値は使用しない
            // （公開側の編集画面で「⑤表示する：管理画面からのみ編集可能」で表示されたとき対策）
            if (
                $onlyUsedOption
                && !$formPatternOptionsTable->isCheckedOptionForEvent(
                    $event->get('id'),
                    $reservationOption->get('form_item_id'),
                    $reservationOption->get('option_id')
                )
            ) {
                continue;
            }

            if (
                isset($formItemOptions[$reservationOption->get('option_id')])
                && ((string)$reservationOption->get('form_item_id')) === ((string)$this->getFormItem()->get('id'))
            ) {
                $option = $formItemOptions[$reservationOption->get('option_id')]->get('option');
                $value = $option->get('name');
                if (!$this->isFixedNumberValueOptions($reservationOption->get('option_id'))) {
                    $value .= '：' . $reservationOption->get('number') . $option->get('stock_unit');
                }
                $data[] = $value;
            }
        }
        if (empty($data)) {
            return null;
        }

        return implode($this->delimiter, $data);
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function getFieldsetInputKey()
    {
        $fieldsetInputKey = 'reservations.option_values.' . $this->getFieldsetColumn();

        return $fieldsetInputKey;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $reservationOptionValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $reservationOptionValidator);

        $reservationOptionValidator
            ->requirePresence('empty', false)
            ->allowEmptyString('empty')
            ->add('empty', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $reservationOptionValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->add('value', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getSearchValueOptions()),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $reservationOptionsTable = $this->getTableLocator()->get('ReservationOptions');

        $where = [];

        $empty = Hash::get($inputs, $this->getSearchInputKey() . '.empty');
        if (isset($empty) && ((string)$empty) === (string)Configure::readOrFail('Master.common.flg.on')) {
            $where[] = function ($expression) {
                $reservationsTable = $this->getTableLocator()->get('Reservations');

                $reserveOptionsQuery = $reservationsTable->getAssociation('ReservationOptions')->find();
                $reserveOptionsQuery->select(['ReservationOptions.reservation_id']);
                $reserveOptionsQuery->where([
                    'ReservationOptions.reservation_id = Reservations.id',
                    'ReservationOptions.option_id IN' => array_keys($this->getSearchValueOptions()),
                    'ReservationOptions.form_item_id' => $this->getFormItem()->get('id'),
                ]);
                $expression->notExists($reserveOptionsQuery);

                return $expression;
            };
        }

        $values = Hash::get($inputs, $this->getSearchInputKey() . '.value');
        if (is_scalar($values) && ((string)$values !== '') || is_array($values) && !empty($values)) {
            $optionIds = [];
            foreach ((array)$values as $value) {
                if (is_scalar($value) && ((string)$value !== '') || is_array($value) && !empty($value)) {
                    foreach ((array)$value as $optionId) {
                        $optionIds[] = $optionId;
                    }
                }
            }
            if (count($optionIds) > 0) {
                $reserveOptionsQuery = $reservationOptionsTable->find();
                $reserveOptionsQuery->select(['ReservationOptions.reservation_id']);
                $reserveOptionsQuery->where([
                    'ReservationOptions.option_id IN' => $optionIds,
                ]);
                $where[] = [
                    'Reservations.id IN' => $reserveOptionsQuery,
                ];
            }
        }

        if (!empty($where)) {
            $query->where(['OR' => $where]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function filterInputs(array $inputs)
    {
        if (!$this->canInput()) {
            return $inputs;
        }

        $result = $inputs;

        $formItemKey = $this->getFieldsetColumn();
        if (isset($result[$formItemKey]) && is_array($result[$formItemKey])) {
            // 単一選択は選択されたデータのみ保持
            if (!$this->getFormItem()->get('form_item_option_group')->isMultipleType()) {
                $reservationOptions = null;
                if (isset($result[$formItemKey]['option_id']) && is_scalar($result[$formItemKey]['option_id'])) {
                    $optionId = $result[$formItemKey]['option_id'];
                    $optionKey = 'value_' . $result[$formItemKey]['option_id'];
                    $reservationOptions = [
                        $optionKey => [
                            'option_id' => $optionId,
                        ],
                    ];
                    if (
                        isset($result[$formItemKey]['reservation_options'][$optionKey])
                        && is_array($result[$formItemKey]['reservation_options'][$optionKey])
                    ) {
                        $reservationOptions[$optionKey] += $result[$formItemKey]['reservation_options'][$optionKey];
                    }
                }
                $result[$formItemKey]['reservation_options'] = $reservationOptions;
            }

            // 予約しない入力値の削除
            if (
                isset($result[$formItemKey]['reservation_options'])
                && is_array($result[$formItemKey]['reservation_options'])
            ) {
                foreach ($result[$formItemKey]['reservation_options'] as $optionKey => $reservationOptions) {
                    if (!isset($reservationOptions['number']) || $reservationOptions['number'] === '') {
                        unset($result[$formItemKey]['reservation_options'][$optionKey]);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = false;
        if (
            $this->getFormItem()->get('form_item_option_group')->isMultipleType()
            && $this->getFormItem()->isRequiredItem()
        ) {
            $isRequired = true;
        }

        if ($this->isAdmin()) {
            $isRequired = false;
        }

        $optionValuesValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY_SELECT), !$isRequired)
            ->addNested($this->getFieldsetColumn(), $optionValuesValidator);

        $isOptionIdRequired = false;
        if (!$this->getFormItem()->get('form_item_option_group')->isMultipleType()) {
            if ($this->getEmptyOptionValue() === false) {
                $isOptionIdRequired = true;
            }

            $optionValuesValidator
                ->requirePresence('option_id', $isOptionIdRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString('option_id', __(Message::ERROR_NOT_EMPTY_SELECT), !$isOptionIdRequired)
                ->add('option_id', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => [
                            'inList',
                            array_keys($this->getOptionValueOptions()),
                        ],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        } else {
            $isOptionIdRequired = $isRequired;

            $optionValuesValidator
                ->requirePresence('option_id', false)
                ->allowEmptyString('option_id')
                ->add('option_id', [
                    'isArray' => [
                        'rule' => ['isArray'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'multiple' => [
                        'rule' => ['multiple', [
                            'in' => array_keys($this->getOptionValueOptions()),
                        ]],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        $reservationOptionsValidator = new KuchenValidator();
        $optionValuesValidator
            ->requirePresence('reservation_options', $isOptionIdRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyArray('reservation_options', __(Message::ERROR_NOT_EMPTY_SELECT), !$isOptionIdRequired)
            ->addNested('reservation_options', $reservationOptionsValidator);

        foreach (array_keys($this->getOptionValueOptions()) as $optionId) {
            $isNumberRequired = function ($context) use ($optionId) {
                if ($this->getEmptyNumberValue($optionId) !== false) {
                    return false;
                }
                if (!$this->getFormItem()->get('form_item_option_group')->isMultipleType()) {
                    if (!isset($context['data']['option_id'])) {
                        return false;
                    }
                    if (((string)$context['data']['option_id']) !== ((string)$optionId)) {
                        return false;
                    }
                }

                return true;
            };

            $reservationOptionValidator = new KuchenValidator();
            $reservationOptionsValidator
                ->requirePresence('value_' . $optionId, $isNumberRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyArray(
                    'value_' . $optionId,
                    __(Message::ERROR_NOT_EMPTY_SELECT),
                    function ($context) use ($isNumberRequired) {
                        return !call_user_func($isNumberRequired, $context);
                    }
                )
                ->addNested('value_' . $optionId, $reservationOptionValidator);

            $reservationOptionValidator
                ->requirePresence('number', $isNumberRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
                ->allowEmptyString(
                    'number',
                    __(Message::ERROR_NOT_EMPTY_SELECT),
                    function ($context) use ($isNumberRequired) {
                        return !call_user_func($isNumberRequired, $context);
                    }
                )
                ->add('number', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'inList' => [
                        'rule' => ['inList', array_keys($this->getNumberValueOptions($optionId))],
                        'last' => true,
                        'message' => __(Message::ERROR_IN_LIST),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        $reservationOptions = $reservation->get('reservation_options');
        if (!isset($reservationOptions)) {
            return null;
        }

        $formItemOptions = $this->getFormItemOptions();

        $data = [];
        foreach ($reservationOptions as $reservationOption) {
            if (
                isset($formItemOptions[$reservationOption->get('option_id')])
                && ((string)$reservationOption->get('form_item_id')) === ((string)$this->getFormItem()->get('id'))
            ) {
                $option = $formItemOptions[$reservationOption->get('option_id')]->get('option');
                $data[] = $this->csvFormat()->csvForMultiple([
                    $this->csvFormat()->csvForId($option->get('id'), $option->get('name')),
                    $reservationOption->get('number'),
                ]);
            }
        }
        if (empty($data)) {
            return null;
        }

        return $this->csvFormat()->csvForHasMany($data);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        /** @var array $result */
        $result = [
            'option_id' => null,
            'reservation_options' => null,
        ];

        $inputs = $this->csvFormat()->inputsForHasMany($data);
        if (empty($inputs)) {
            return $this->formatAdditionCsvInputData($result);
        }

        foreach ($inputs as $index => $row) {
            $inputs[$index] = $this->csvFormat()->inputsForMultiple($row);
            if (empty($inputs[$index])) {
                unset($inputs[$index]);
            }
        }

        foreach ($inputs as $row) {
            $keys = [
                'option_id',
                'number',
            ];
            $values = [];
            foreach ($keys as $index => $key) {
                $values[$key] = Hash::get($row, (string)$index);
            }
            $values['option_id'] = $this->csvFormat()->inputForId($values['option_id']);

            if (!$this->getFormItem()->get('form_item_option_group')->isMultipleType() && count($inputs) <= 1) {
                $result['option_id'] = $values['option_id'];
            } else {
                $result['option_id'][] = $values['option_id'];
            }
            $result['reservation_options']['value_' . $values['option_id']]['number'] = $values['number'];
        }

        return $this->formatAdditionCsvInputData($result);
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        $description = Configure::readOrFail('Setting.csv.import.sample.reservationOption');

        $valueOptions = [];
        foreach ($this->getFormItemOptions() as $formItemOption) {
            $option = $formItemOption->get('option');

            $valueOptions[$formItemOption->get('option_id')] = $option->get('name');
        }
        if (!empty($valueOptions)) {
            $description .= "\n" . $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
        }

        return $description;
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        $messages = Hash::get($errors, 'option_values.' . $this->getFieldsetColumn(), []);
        foreach ($this->getFormItemOptions() as $formItemOption) {
            $optionId = $formItemOption->get('option_id');
            if (isset($errors['option_errors_' . $optionId])) {
                $messages['option_errors_' . $optionId] = $errors['option_errors_' . $optionId];
            }
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return $this->getAdditionMailReplaceToken();
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $reservationOptions = Hash::get($data, 'reservation.reservation_options');
        if (!isset($reservationOptions)) {
            return null;
        }

        $formItemOptions = $this->getFormItemOptions();

        $data = [];
        foreach ($reservationOptions as $reservationOption) {
            if (
                isset($formItemOptions[$reservationOption['option_id']])
                && ((string)$reservationOption['form_item_id']) === ((string)$this->getFormItem()->get('id'))
            ) {
                $option = $formItemOptions[$reservationOption['option_id']]->get('option');
                $data[] = $option->get('name') . '：' . $reservationOption['number'] . $option->get('stock_unit');
            }
        }
        if (empty($data)) {
            return null;
        }

        return implode("\n", $data);
    }

    /**
     * @inheritDoc
     */
    public function getCalendarOutputValue(Reservation $reservation)
    {
        return $this->getDetailValue([
            'user' => $reservation->get('user'),
            'reservation' => $reservation,
        ]);
    }

    /**
     * オプションの選択肢を取得
     *
     * @return array 選択肢
     */
    public function getOptionValueOptions()
    {
        if (!isset($this->optionValueOptions)) {
            /** @var \App\Model\Table\FormPatternOptionsTable $formPatternOptionsTable */
            $formPatternOptionsTable = $this->getTableLocator()->get('FormPatternOptions');

            $event = $this->getConfig('event');
            $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($this->getConfig('usageTimestampFrom'));
            if (!isset($usageTimestampFrom)) {
                throw new CakeException();
            }
            $reservation = $this->getConfig('reservation');

            $includeValues = null;
            if (isset($reservation)) {
                $oldEventId = $reservation->get('event_id');
                $oldUsageTimestampFrom = DateTimeUtility::convertToDateTimeObject(
                    $reservation->get('usage_timestamp_from')
                );
                if (!isset($oldUsageTimestampFrom)) {
                    throw new CakeException();
                }
                if (
                    (string)$event->get('id') === ((string)$oldEventId)
                    && $usageTimestampFrom->format('Y-m-d H:i:s') === $oldUsageTimestampFrom->format('Y-m-d H:i:s')
                ) {
                    $optionValues = $reservation->get('option_values');
                    if (isset($optionValues[$this->getFieldsetColumn()]['reservation_options'])) {
                        $includeValues = array_keys(
                            $optionValues[$this->getFieldsetColumn()]['reservation_options']
                        );
                    }
                }
            }

            $formItemOptionIds = $formPatternOptionsTable->find('eventOptionsList', [
                'inputs' => [
                    'event_id' => $event->get('id'),
                    'usage_timestamp_from' => $usageTimestampFrom->format('Y-m-d H:i:s'),
                    'public_flg' => !$this->isAdmin(),
                    'include_values' => $includeValues,
                ],
            ])->all()->combine('form_item_option_id', 'form_item_option_id')->toArray();

            $optionValueOptions = [];
            foreach ($this->getFormItemOptions() as $formItemOption) {
                $option = $formItemOption->get('option');

                if (isset($formItemOptionIds[$formItemOption->get('id')])) {
                    $optionValueOptions[$formItemOption->get('option_id')] = $option->get('name');
                }
            }

            $this->optionValueOptions = $optionValueOptions;
        }

        return $this->optionValueOptions;
    }

    /**
     * 予約数の選択肢を取得
     *
     * @param int $optionId オプションID
     * @return array 選択肢
     */
    public function getNumberValueOptions(int $optionId)
    {
        if (!isset($this->numberValueOptions)) {
            $numberValueOptions = [];
            foreach ($this->getFormItemOptions() as $formItemOption) {
                $option = $formItemOption->get('option');

                $number = max($formItemOption->get('stock_range_from'), 1);
                $limit = $formItemOption->get('stock_range_to');
                while ($number <= $limit) {
                    $numberValueOptions[$option->get('id')][$number] = $number . $option->get('stock_unit');
                    $number += 1;
                }
            }

            $this->numberValueOptions = $numberValueOptions;
        }

        return Hash::get($this->numberValueOptions, (string)$optionId, []);
    }

    /**
     * 予約数の選択肢の固定を判定
     *
     * @param int $optionId オプションID
     * @return bool
     */
    public function isFixedNumberValueOptions(int $optionId)
    {
        if ($this->getEmptyNumberValue($optionId) !== false || count($this->getNumberValueOptions($optionId)) > 1) {
            return false;
        }

        return true;
    }

    /**
     * 空のオプション選択肢を取得
     *
     * @return mixed 選択肢
     */
    public function getEmptyOptionValue()
    {
        if ($this->getFormItem()->get('form_item_option_group')->isMultipleType()) {
            return false;
        }
        if ($this->getFormItem()->isRequiredItem()) {
            return false;
        }

        return __('reservation/option/notReserve');
    }

    /**
     * 空の予約数選択肢を取得
     *
     * @param int $optionId オプションID
     * @return mixed 選択肢
     */
    public function getEmptyNumberValue(int $optionId)
    {
        if (!$this->getFormItem()->get('form_item_option_group')->isMultipleType()) {
            return false;
        }

        $formItemOptions = $this->getFormItemOptions();
        if ($formItemOptions[$optionId]->get('stock_range_from') > 0) {
            return false;
        }

        return __('reservation/option/notReserve');
    }

    /**
     * 検索用の選択肢を取得
     *
     * @return array 選択肢
     */
    public function getSearchValueOptions()
    {
        if (!isset($this->searchValueOptions)) {
            $searchValueOptions = [];
            foreach ($this->getFormItemOptions() as $formItemOption) {
                $option = $formItemOption->get('option');

                $searchValueOptions[$formItemOption->get('option_id')] = $option->get('name');
            }

            $this->searchValueOptions = $searchValueOptions;
        }

        return $this->searchValueOptions;
    }

    /**
     * オプションの説明文を取得
     *
     * @param int $optionId オプションID
     * @return string|null 説明文
     */
    public function getOptionDescription(int $optionId)
    {
        $formItemOptions = $this->getFormItemOptions();
        $description = $formItemOptions[$optionId]->get('option')->get('description');

        return $description;
    }

    /**
     * オプション一覧を取得
     *
     * @return array オプション一覧
     */
    protected function getFormItemOptions()
    {
        if (!isset($this->formItemOptions)) {
            /** @var \App\Model\Table\FormItemOptionsTable $formItemOptionsTable */
            $formItemOptionsTable = $this->getTableLocator()->get('FormItemOptions');

            $optionsList = $formItemOptionsTable->find('optionsList', [
                'inputs' => [
                    'form_item_option_group_id' => $this->getFormItem()->get('form_item_option_group')->get('id'),
                ],
            ]);

            $formItemOptions = [];
            foreach ($optionsList as $formItemOption) {
                $formItemOptions[$formItemOption->get('option')->get('id')] = $formItemOption;
            }

            $this->formItemOptions = $formItemOptions;
        }

        return $this->formItemOptions;
    }
}
