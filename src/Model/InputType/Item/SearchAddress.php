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
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * SearchAddress class.
 */
class SearchAddress extends AbstractInputTypeItem implements
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

    public const SEARCH_ADDRESS_ZIP_VALUE_MAX = 7;
    public const SEARCH_ADDRESS_MUNICIPALITY_VALUE_MAX = 300;
    public const SEARCH_ADDRESS_TOWN_VALUE_MAX = 300;
    public const SEARCH_ADDRESS_BUILDING_VALUE_MAX = 300;
    public const SEARCH_ADDRESS_ZIP_SEARCH_VALUE_MAX = 1000;
    public const SEARCH_ADDRESS_MUNICIPALITY_SEARCH_VALUE_MAX = 1000;
    public const SEARCH_ADDRESS_TOWN_SEARCH_VALUE_MAX = 1000;
    public const SEARCH_ADDRESS_BUILDING_SEARCH_VALUE_MAX = 1000;

    /**
     * @var string
     */
    protected $columnType = 'json';

    /**
     * @var array
     */
    protected $dataKeys = [
        'zip',
        'prefecture',
        'municipality',
        'town',
        'building',
    ];

    /**
     * @var string
     */
    protected $delimiter = "\n";

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        return $this->getAdditionValue($options);
    }

    /**
     * @inheritDoc
     */
    public function getListClass()
    {
        $class = Configure::read('Master.adminListItems.formTypeItemsClass.' . $this->getFormItem()->get('input_type'));

        return (string)$class;
    }

    /**
     * @inheritDoc
     */
    public function getListValue(?array $options = null)
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $data = $this->getAdditionValue($options);
        if (!isset($data)) {
            return null;
        }

        $values = [];
        foreach ($this->dataKeys as $key) {
            $values[$key] = Hash::get($data, $key);
            if ($key === 'prefecture' && ((string)$values[$key]) !== '') {
                $values[$key] = $prefecturesTable->getPrefectureName((int)$values[$key]);
            }
        }

        return implode($this->delimiter, $values);
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $searchAddressValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $searchAddressValidator);

        $searchAddressValidator
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
        $searchAddressValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->addNested('value', $valueValidator);

        $valueValidator
            ->requirePresence('zip', false)
            ->allowEmptyString('zip')
            ->add('zip', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_ZIP_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_ZIP_SEARCH_VALUE_MAX),
                ],
            ]);

        $valueValidator
            ->requirePresence('prefecture', false)
            ->allowEmptyString('prefecture')
            ->add('prefecture', [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($prefecturesTable->getValueOptions()),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $valueValidator
            ->requirePresence('municipality', false)
            ->allowEmptyString('municipality')
            ->add('municipality', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_MUNICIPALITY_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_MUNICIPALITY_SEARCH_VALUE_MAX),
                ],
            ]);

        $valueValidator
            ->requirePresence('town', false)
            ->allowEmptyString('town')
            ->add('town', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_TOWN_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_TOWN_SEARCH_VALUE_MAX),
                ],
            ]);

        $valueValidator
            ->requirePresence('building', false)
            ->allowEmptyString('building')
            ->add('building', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_BUILDING_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_BUILDING_SEARCH_VALUE_MAX),
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
        foreach ($this->dataKeys as $key) {
            $value = Hash::get($inputs, $this->getSearchInputKey() . '.value.' . $key);
            if (isset($value) && $value !== '') {
                $bindName = $this->getNextSearchBindName();
                $query->bind($bindName, $key, 'string');

                $column = $this->driverExpression()->jsonValue('value', $bindName);
                if ($key === 'prefecture') {
                    $valueWhere[] = $this->getAdditionSearchExpression(
                        $query,
                        function ($expression) use ($column, $value) {
                            $expression->in($column, $value);

                            return $expression;
                        }
                    );
                } else {
                    $valueWhere[] = $this->getAdditionSearchExpression(
                        $query,
                        function ($expression) use ($column, $value) {
                            $expression->like($column, '%' . $this->driverExpression()->escapeLike($value) . '%');

                            return $expression;
                        }
                    );
                }
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
        foreach ($this->dataKeys as $key) {
            $result[$key] = Hash::get($inputs, $this->getFieldsetColumn() . '.' . $key);
        }
        if (is_string($result['zip']) && preg_match('/^[0-9]{3}-[0-9]{4}$/', $result['zip']) === 1) {
            $result['zip'] = preg_replace('/-/', '', $result['zip']);
        }
        if (json_encode($result) === false) {
            $this->isInvalidData = true;
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
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();

        $requireFunction = function ($context) use ($isRequired) {
            if (isset($context['data']) && is_array($context['data'])) {
                if (!empty(array_filter($context['data']))) {
                    return false;
                }
            }

            return $isRequired;
        };

        $searchAddressValidartor = new KuchenValidator();
        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->addNested($this->getFieldsetColumn(), $searchAddressValidartor);

        $searchAddressValidartor
            ->requirePresence('zip', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zip', __(Message::ERROR_NOT_EMPTY), $requireFunction)
            ->add('zip', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'zipCode' => [
                    'rule' => ['inputCheck', FormItemDetail::TEXT_INPUT_CHECK_ZIP_CODE],
                    'last' => true,
                    'message' => __(Message::ERROR_ZIP_CODE),
                ],
            ]);

        $searchAddressValidartor
            ->requirePresence('prefecture', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('prefecture', __(Message::ERROR_NOT_EMPTY), $requireFunction)
            ->add('prefecture', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($prefecturesTable->getValueOptions())],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $searchAddressValidartor
            ->requirePresence('municipality', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('municipality', __(Message::ERROR_NOT_EMPTY), $requireFunction)
            ->add('municipality', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_MUNICIPALITY_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_MUNICIPALITY_VALUE_MAX),
                ],
            ]);

        $searchAddressValidartor
            ->requirePresence('town', $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('town', __(Message::ERROR_NOT_EMPTY), $requireFunction)
            ->add('town', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_TOWN_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_TOWN_VALUE_MAX),
                ],
            ]);

        $searchAddressValidartor
            ->requirePresence('building', false)
            ->allowEmptyString('building')
            ->add('building', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SEARCH_ADDRESS_BUILDING_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::SEARCH_ADDRESS_BUILDING_VALUE_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        $data = $this->getAdditionValue($options);
        if (!isset($data)) {
            return null;
        }

        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $values = [];
        foreach ($this->dataKeys as $key) {
            if (isset($data[$key])) {
                $values[$key] = $data[$key];
            }
        }
        if (isset($values['prefecture'])) {
            $values['prefecture'] = $this->csvFormat()->csvForId(
                $values['prefecture'],
                $prefecturesTable->getPrefectureName((int)$values['prefecture'])
            );
        }

        return $this->csvFormat()->csvForMultiple($values);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $inputs = $this->csvFormat()->inputsForMultiple($data);
        if (empty($inputs)) {
            return null;
        }

        $values = [];
        foreach ($this->dataKeys as $index => $key) {
            $values[$key] = Hash::get($inputs, $index);
        }
        $values['prefecture'] = $this->csvFormat()->inputForId($values['prefecture']);

        return $this->formatAdditionCsvInputData($values);
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $prefectureValueOptions = $prefecturesTable->getValueOptions();
        $prefecture = $this->csvFormat()->csvForHasManyId(array_keys($prefectureValueOptions), $prefectureValueOptions);

        return Configure::readOrFail('Setting.csv.import.sample.address') . "\n" . $prefecture;
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
        return $this->getListValue($data);
    }

    /**
     * @inheritDoc
     */
    public function getCalendarOutputValue(Reservation $reservation)
    {
        return $this->getListValue([
            'user' => $reservation->get('user'),
            'reservation' => $reservation,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLoginNameValue(User $user)
    {
        return $this->getListValue([
            'user' => $user,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getAppValue(?array $options = null)
    {
        return $this->getListValue($options);
    }
}
