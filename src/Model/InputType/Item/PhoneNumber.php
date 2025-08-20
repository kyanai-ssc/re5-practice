<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\Reservation;
use App\Model\Entity\User;
use App\Model\InputType\AbstractInputTypeItem;
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
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * PhoneNumber class.
 */
class PhoneNumber extends AbstractInputTypeItem implements
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

    public const PHONE_NUMBER_VALUE_0_MAX = 5;
    public const PHONE_NUMBER_VALUE_1_MAX = 4;
    public const PHONE_NUMBER_VALUE_2_MAX = 4;
    public const PHONE_NUMBER_SEARCH_VALUE_MAX = 1000;

    /**
     * @var int
     */
    protected $fieldCount = 3;

    /**
     * @var string
     */
    protected $delimiter = '-';

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
        if (!is_array($value) || count($value) !== $this->getFieldCount()) {
            return null;
        }

        $hasValue = false;
        foreach ($value as $data) {
            if (isset($data) && $data !== '') {
                $hasValue = true;
                break;
            }
        }
        if (!$hasValue) {
            return null;
        }

        return implode('-', $value);
    }

    /**
     * @inheritDoc
     */
    public function valueToPHP($value)
    {
        if (!is_scalar($value) || ((string)$value) === '') {
            return null;
        }

        $detailValue = [];
        $value = preg_split('/-/', (string)$value);
        if ($value !== false) {
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
        $phoneNumberValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $phoneNumberValidator);

        $phoneNumberValidator
            ->requirePresence('empty', false)
            ->allowEmptyString('empty')
            ->add('empty', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $phoneNumberValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->add('value', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::PHONE_NUMBER_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::PHONE_NUMBER_SEARCH_VALUE_MAX),
                ],
            ]);

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

        $valueWhere = $this->buildAdditionLikeSearchQuery($query, $inputs);
        if (isset($valueWhere)) {
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
            $result['value_' . $i] = Hash::get($inputs, $this->getFieldsetColumn() . '.value_' . $i);
            if (is_scalar($result['value_' . $i])) {
                $result['value_' . $i] = mb_convert_kana((string)$result['value_' . $i], 'n');
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

        $phoneNumberValidartor = new KuchenValidator();
        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->addNested($this->getFieldsetColumn(), $phoneNumberValidartor);

        $phoneNumberValidartor
            ->requirePresence('value_0', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('value_0', __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add('value_0', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::PHONE_NUMBER_VALUE_0_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::PHONE_NUMBER_VALUE_0_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', '/^[0-9]+$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        $phoneNumberValidartor
            ->requirePresence('value_1', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('value_1', __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add('value_1', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::PHONE_NUMBER_VALUE_1_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::PHONE_NUMBER_VALUE_1_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', '/^[0-9]+$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'phoneNumber' => [
                    'rule' => function ($value, $context) use ($phoneNumberValidartor) {
                        if (!$phoneNumberValidartor->isValid('value_0')) {
                            return true;
                        }
                        $first = Hash::get($context['data'], 'value_0');

                        return CustomValidation::phoneNumberFirstTwoDigits($first . '-' . $value);
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_PHONE_NUMBER),
                ],
            ]);

        $phoneNumberValidartor
            ->requirePresence('value_2', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('value_2', __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add('value_2', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::PHONE_NUMBER_VALUE_2_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::PHONE_NUMBER_VALUE_2_MAX),
                ],
                'custom' => [
                    'rule' => ['custom', '/^[0-9]+$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
                'phoneNumber' => [
                    'rule' => function ($value, $context) use ($phoneNumberValidartor) {
                        $values = [];
                        for ($i = 0; $i < $this->fieldCount - 1; ++$i) {
                            if (!$phoneNumberValidartor->isValid('value_' . $i)) {
                                return true;
                            }
                            $values[$i] = Hash::get($context['data'], 'value_' . $i);
                        }
                        $values[$this->fieldCount - 1] = $value;

                        return CustomValidation::phoneNumberWithHyphen(implode('-', $values));
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_PHONE_NUMBER),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        if (((string)$data) === '') {
            return null;
        }

        $detailValue = [];
        $value = preg_split('/-/', (string)$data, $this->getFieldCount());
        if ($value !== false) {
            foreach ($value as $key => $value) {
                $detailValue['value_' . $key] = $value;
            }
        }

        return $this->formatAdditionCsvInputData($detailValue);
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.phoneNumber');
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
}
