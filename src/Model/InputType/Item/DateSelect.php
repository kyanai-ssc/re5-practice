<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormItemDetail;
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
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * DateSelect class.
 */
class DateSelect extends AbstractInputTypeItem implements
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

    /**
     * @var string
     */
    protected $columnType = 'date';

    /**
     * @var array|null
     */
    protected $valueOptions = null;

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $data = $this->getAdditionValue($options);
        if (!isset($data)) {
            return null;
        }

        $data = DateTimeUtility::convertToDateObject($data);
        if (!isset($data)) {
            return null;
        }

        return $data->format('Y/m/d');
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
    public function buildSearchValidator(Validator $validator)
    {
        $dateSelectValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $dateSelectValidator);

        $dateSelectValidator
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
        $dateSelectValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->addNested('value', $valueValidator);

        $valueValidator
            ->requirePresence('from', false)
            ->allowEmptyDate('from')
            ->add('from', [
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

        $valueValidator
            ->requirePresence('to', false)
            ->allowEmptyDate('to')
            ->add('to', [
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
                    'rule' => ['compareDateTimeFields', 'from', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATETIME),
                    'on' => function () use ($valueValidator) {
                        return $valueValidator->isValid('from');
                    },
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

        $valueWhere = [];
        $dateFrom = Hash::get($inputs, $this->getSearchInputKey() . '.value.from');
        if (isset($dateFrom) && $dateFrom !== '') {
            $valueWhere[] = $this->getAdditionSearchExpression($query, function ($expression) use ($dateFrom) {
                $expression->gte($this->driverExpression()->cast('value', TableSchema::TYPE_DATE), $dateFrom);

                return $expression;
            });
        }
        $dateTo = Hash::get($inputs, $this->getSearchInputKey() . '.value.to');
        if (isset($dateTo) && $dateTo !== '') {
            $valueWhere[] = $this->getAdditionSearchExpression($query, function ($expression) use ($dateTo) {
                $expression->lte($this->driverExpression()->cast('value', TableSchema::TYPE_DATE), $dateTo);

                return $expression;
            });
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
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();
        $lowerLimit = $this->getLowerLimit();
        $upperLimit = $this->getUpperLimit();

        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getFieldsetColumn(), [
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
                'compareGreaterOrEqual' => [
                    'rule' => ['compareDateTime', Validation::COMPARE_GREATER_OR_EQUAL, $lowerLimit],
                    'last' => true,
                    'message' => __(Message::ERROR_UNDER_DATE, $lowerLimit),
                ],
                'compareLessOrEqual' => [
                    'rule' => ['compareDateTime', Validation::COMPARE_LESS_OR_EQUAL, $upperLimit],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DATE, $upperLimit),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        $data = $this->getDetailValue($options);
        if (!isset($data)) {
            return null;
        }

        return $this->csvFormat()->csvForDate($data);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        if (isset($data)) {
            $data = DateTimeUtility::zeroPaddingDate($data);
        }

        return $this->formatAdditionCsvInputData($data);
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.date');
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
     * 選択肢を取得
     *
     * @param string|null $key キー
     * @return array 選択肢
     */
    public function getValueOptions($key = null)
    {
        if (!isset($this->valueOptions)) {
            $valueOptions = [];

            $this->valueOptions = $valueOptions;
        }

        if (!isset($key)) {
            return $this->valueOptions;
        }

        return Hash::get($this->valueOptions, $key, []);
    }

    /**
     * 初期値を取得
     *
     * @return \Cake\I18n\FrozenDate|null 初期値
     */
    public function getDefaultValue()
    {
        /** @var \Cake\Datasource\EntityInterface $formItemDetail */
        $formItemDetail = $this->getFormItemDetails(0);

        $date = DateTimeUtility::convertToDateObject($formItemDetail->get('date_default'));

        return $date;
    }

    /**
     * 開始日を取得
     *
     * @return \Cake\I18n\FrozenDate|null 開始日
     */
    public function getLowerLimit()
    {
        /** @var \Cake\Datasource\EntityInterface $formItemDetail */
        $formItemDetail = $this->getFormItemDetails(0);

        $lowerLimit = DateTimeUtility::convertToDateObject($formItemDetail->get('date_lower_limit'));

        return $lowerLimit;
    }

    /**
     * 終了日を取得
     *
     * @return \Cake\I18n\FrozenDate|null 終了日
     */
    public function getUpperLimit()
    {
        /** @var \Cake\Datasource\EntityInterface $formItemDetail */
        $formItemDetail = $this->getFormItemDetails(0);

        $upperLimit = null;
        $upperLimitType = $formItemDetail->get('date_upper_limit_type');
        if (((string)$upperLimitType) === ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE)) {
            $upperLimit = $formItemDetail->get('date_upper_limit_absolute');
            $upperLimit = new FrozenDate($upperLimit);
        }

        if (((string)$upperLimitType) === ((string)FormItemDetail::DATE_UPPER_LIMIT_TYPE_RELATIVE)) {
            $now = $this->commonData()->getNowDateTime();
            $upperLimit = new FrozenDate($now->format('Y-m-01'));
            $upperLimit = $upperLimit->addYears($formItemDetail->get('date_upper_limit_relative'));
            if ($now->format('j') < $now->format('t') && $now->format('j') < $upperLimit->format('t')) {
                $upperLimit = $upperLimit->day((int)$now->format('j'));
            } else {
                $upperLimit = $upperLimit->lastOfMonth();
            }
        }

        return $upperLimit;
    }
}
