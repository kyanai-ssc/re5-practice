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
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Radio class.
 */
class Radio extends AbstractInputTypeItem implements
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
    protected $delimiter = "\n";

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $data = $this->getAdditionValue($options);
        if (!isset($data)) {
            return null;
        }

        $values = array_intersect_key($this->getValueOptions(), array_fill_keys((array)$data, true));
        if (empty($values)) {
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
    public function buildSearchValidator(Validator $validator)
    {
        $radioValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $radioValidator);

        $radioValidator
            ->requirePresence('empty', false)
            ->allowEmptyString('empty')
            ->add('empty', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $radioValidator
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
                        'in' => array_keys($this->getValueOptions()),
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
        $where = [];

        $emptyWhere = $this->buildAdditionEmptySearchQuery($query, $inputs);
        if (isset($emptyWhere)) {
            $where[] = $emptyWhere;
        }

        $valueWhere = $this->buildAdditionInSearchQuery($query, $inputs);
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
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();

        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY_SELECT), !$isRequired)
            ->add($this->getFieldsetColumn(), [
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
        $data = $this->getAdditionValue($options);
        if (!isset($data)) {
            return null;
        }

        $values = array_intersect_key($this->getValueOptions(), array_fill_keys((array)$data, true));
        if (empty($values)) {
            return null;
        }

        $result = [];
        foreach ($values as $key => $value) {
            $result[] = $this->csvFormat()->csvForId($key, $value);
        }

        return $this->csvFormat()->csvForMultiple($result);
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        return $this->formatAdditionCsvInputData($this->csvFormat()->inputForId($data));
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        $valueOptions = $this->getValueOptions();

        return $this->csvFormat()->csvForHasManyId(array_keys($valueOptions), $valueOptions);
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
     * @return array 選択肢
     */
    public function getValueOptions()
    {
        if (!$this->getFormItem()->has('form_item_choices')) {
            return [];
        }

        $formItemChoices = $this->getFormItem()->get('form_item_choices');
        if (!is_array($formItemChoices)) {
            $formItemChoices = [$formItemChoices];
        }

        $valueOptions = [];
        foreach ($formItemChoices as $formItemChoice) {
            if ($formItemChoice instanceof EntityInterface) {
                $valueOptions[$formItemChoice->get('id')] = $formItemChoice->get('name');
            }
        }

        return $valueOptions;
    }
}
