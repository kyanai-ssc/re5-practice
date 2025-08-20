<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
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
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * ReservationNumber class.
 */
class ReservationNumber extends AbstractInputTypeItem implements
    ChargeTypeInterface,
    CsvInputInterface,
    CsvOutputInterface,
    InputInterface,
    ListOutputInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use InputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    public const RESERVATION_NUMBER_SEARCH_VALUE_MAX = 10000000;

    /**
     * @var string
     */
    protected $tableName = 'reservations';

    /**
     * @var string
     */
    protected $columnName = 'number';

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        $event = $this->getConfig('event');
        if (!$this->getConfig('isApp', false)) {
            if (!$event->canHideNumberInput()) {
                $this->displayType['canDisplay'] = true;
                $this->displayType['canInput'] = true;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        return $reservation->get('number');
    }

    /**
     * @inheritDoc
     */
    public function canSort()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSortKey()
    {
        return 'Reservations.number';
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
        $reservationNumberValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $reservationNumberValidator);

        $reservationNumberValidator
            ->requirePresence('from', false)
            ->allowEmptyString('from')
            ->add('from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::RESERVATION_NUMBER_SEARCH_VALUE_MAX,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::RESERVATION_NUMBER_SEARCH_VALUE_MAX),
                ],
            ]);

        $reservationNumberValidator
            ->requirePresence('to', false)
            ->allowEmptyString('to')
            ->add('to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'compareLessOrEqual' => [
                    'rule' => [
                        'comparison',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        static::RESERVATION_NUMBER_SEARCH_VALUE_MAX,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, static::RESERVATION_NUMBER_SEARCH_VALUE_MAX),
                ],
                'compareFields' => [
                    'rule' => ['compareFields', 'from', '>='],
                    'last' => true,
                    'message' => __(Message::ERROR_LESS_THAN_FROM),
                    'on' => function () use ($reservationNumberValidator) {
                        // 比較対象にエラーがある場合は比較処理を行わない
                        return $reservationNumberValidator->isValid('from');
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
        $numberFrom = Hash::get($inputs, $this->getSearchInputKey() . '.from');
        if (isset($numberFrom) && $numberFrom !== '') {
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() . ' >=' => $numberFrom,
            ]);
        }

        $numberTo = Hash::get($inputs, $this->getSearchInputKey() . '.to');
        if (isset($numberTo) && $numberTo !== '') {
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() . ' <=' => $numberTo,
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getColumnName(), true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString($this->getColumnName(), __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add($this->getColumnName(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getValueOptions())],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
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
        $result = [];
        $result['reservations']['number'] = $data;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.values');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'number', []);
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'reserve_number';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'reservation.number');
    }

    /**
     * 選択肢を取得
     *
     * @return array 選択肢
     */
    public function getValueOptions()
    {
        $event = $this->getConfig('event');

        return $event->getNumberValueOptions();
    }

    /**
     * 選択肢の固定を判定
     *
     * @return bool
     */
    public function isFixedValueOptions()
    {
        $event = $this->getConfig('event');

        return $event->isFixedNumber();
    }
}
