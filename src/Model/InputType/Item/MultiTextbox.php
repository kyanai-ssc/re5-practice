<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\Reservation;
use App\Model\Entity\User;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\AbstractInputTypeManager;
use App\Model\InputType\Item\Type\AdditionTypeInterface;
use App\Model\InputType\Item\Type\AdditionTypeTrait;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\LoginNameInterface;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * MultiTextbox class.
 */
class MultiTextbox extends AbstractInputTypeItem implements
    AdditionTypeInterface,
    CalendarOutputInterface,
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
    LoginNameInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use AdditionTypeTrait;
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    public const MULTI_TEXT_SEARCH_VALUE_MAX = 1000;

    /**
     * @var mixed
     */
    protected $fieldCount = null;

    /**
     * @var string
     */
    protected $delimiter = "\n";

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        parent::initialize();

        $this->fieldCount = count($this->getFormItem()->get('form_item_details'));
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $values = $this->getAdditionValue($options);
        if (!isset($values)) {
            return null;
        }

        return implode($this->delimiter, $values);
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
    public function valueToDatabase($value)
    {
        if (!is_array($value)) {
            return null;
        }

        $result = [];
        foreach ($value as $key => $data) {
            $key = preg_replace('/^value_/', '', $key);
            if (ctype_digit($key)) {
                $result[$key] = $data;
                $hasValue = true;
            }
        }
        ksort($result, SORT_NUMERIC);

        $hasValue = false;
        foreach ($result as $data) {
            if (isset($data) && $data !== '') {
                $hasValue = true;
            }
        }
        if (!$hasValue) {
            return null;
        }

        return json_encode($result);
    }

    /**
     * @inheritDoc
     */
    public function valueToPHP($value)
    {
        if (!is_scalar($value) || (string)$value === '') {
            return null;
        }

        $detailValue = [];
        $value = json_decode((string)$value, true);
        if (is_array($value)) {
            foreach ($value as $key => $data) {
                $detailValue['value_' . $key] = $data;
            }
        }

        return $detailValue;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $multiTextboxValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $multiTextboxValidator);

        $multiTextboxValidator
            ->requirePresence('empty', false)
            ->allowEmptyString('empty')
            ->add('empty', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $valueValidator = new KuchenValidator();
        $multiTextboxValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->addNested('value', $valueValidator);

        $multiTextboxValidator->addNested('value', $valueValidator);
        for ($i = 0; $i < $this->fieldCount; ++$i) {
            $valueValidator
                ->requirePresence('value_' . $i, false)
                ->allowEmptyString('value_' . $i)
                ->add('value_' . $i, [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::MULTI_TEXT_SEARCH_VALUE_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::MULTI_TEXT_SEARCH_VALUE_MAX),
                    ],
                ]);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $where = [];

        $emptyWhere = $this->buildAdditionEmptySearchQuery($query, $inputs);
        if (isset($emptyWhere)) {
            $where[] = $emptyWhere;
        }

        $valueWhere = [];
        for ($i = 0; $i < $this->fieldCount; ++$i) {
            $value = Hash::get($inputs, $this->getSearchInputKey() . '.value.value_' . $i);
            if (isset($value) && $value !== '') {
                $bindName = $this->getNextSearchBindName();
                $query->bind($bindName, $i, 'string');

                $column = $this->driverExpression()->jsonValue('value', $bindName);
                $valueWhere[] = $this->getAdditionSearchExpression(
                    $query,
                    function ($expression) use ($column, $value) {
                        $expression->like($column, '%' . $this->driverExpression()->escapeLike($value) . '%');

                        return $expression;
                    }
                );
            }
        }
        if (!empty($valueWhere)) {
            $where[] = $valueWhere;
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

        $result = [];
        for ($i = 0; $i < $this->fieldCount; ++$i) {
            $inputs = $this->applyInputTranslate(
                $inputs,
                $this->getFieldsetColumn() . '.value_' . $i,
                $this->getFormItemDetails($i)
            );
            $value = Hash::get($inputs, $this->getFieldsetColumn() . '.value_' . $i);
            $result['value_' . $i] = $this->replaceForbiddenString($value);
            if ($this->isNotEmptyInput($value) && !$this->isNotEmptyInput($result['value_' . $i])) {
                $this->isInvalidData = true;
            }
        }

        $notEmpty = false;
        foreach ($result as $value) {
            if (isset($value) && $value !== '') {
                $notEmpty = true;
                break;
            }
        }

        $inputs[$this->getFieldsetColumn()] = null;
        if ($notEmpty) {
            $inputs[$this->getFieldsetColumn()] = $result;
        }

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();

        if ($this instanceof FullName) {
            $requireFunction = function ($context) use ($isRequired) {
                if (isset($context['data']) && is_array($context['data'])) {
                    if (!empty(array_filter($context['data']))) {
                        return false;
                    }
                }

                return $isRequired;
            };
        } else {
            $requireFunction = function () use ($isRequired) {
                if (!$isRequired) {
                    return true;
                } else {
                    return false;
                }
            };
        }

        $multiTextboxValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->addNested($this->getFieldsetColumn(), $multiTextboxValidator);

        for ($i = 0; $i < $this->fieldCount; ++$i) {
            /** @var \Cake\Datasource\EntityInterface $formItemDetail */
            $formItemDetail = $this->getFormItemDetails($i);
            $fieldName = 'value_' . $i;
            $minLength = 0;
            if ($formItemDetail->has('text_lower_limit')) {
                $minLength = $formItemDetail->get('text_lower_limit');
            }
            $maxLength = AbstractInputTypeManager::ITEM_DETAIL_TEXT_UPPER_LIMIT_MAX;
            if ($formItemDetail->has('text_upper_limit')) {
                $maxLength = $formItemDetail->get('text_upper_limit');
            }

            $multiTextboxValidator
                ->requirePresence($fieldName, $isRequired, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString($fieldName, __(Message::ERROR_NOT_EMPTY), $requireFunction)
                ->add($fieldName, [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'minLength' => [
                        'rule' => ['minLength', $minLength],
                        'last' => true,
                        'message' => __(Message::ERROR_MIN_LENGTH, $minLength),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', $maxLength],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, $maxLength),
                    ],
                ]);

            $this->addInputCheckValidation($multiTextboxValidator, $fieldName, $formItemDetail);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        $values = $this->getAdditionValue($options);
        if (!isset($values)) {
            return null;
        }

        return $this->csvFormat()->csvForMultiple($values);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $values = $this->csvFormat()->inputsForMultiple($data);
        if (empty($values)) {
            return null;
        }

        $inputs = [];
        foreach ($values as $key => $value) {
            $inputs['value_' . $key] = $value;
        }

        return $this->formatAdditionCsvInputData($inputs);
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.multiple');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return $this->getAdditionCsvErrorMessages($errors);
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
        return $this->getDetailValue($data);
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
     * @inheritDoc
     */
    public function getLoginNameValue(User $user)
    {
        return $this->getDetailValue([
            'user' => $user,
        ]);
    }

    /**
     * フィールド数を取得
     *
     * @return int フィールド数
     */
    public function getFieldCount()
    {
        return $this->fieldCount;
    }

    /**
     * 利用不可の文字を置換
     *
     * @param mixed $data 入力値
     * @return mixed 置換結果
     */
    protected function replaceForbiddenString($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        return $this->csvFormat()->replaceSeparetorForMultiple($data);
    }
}
