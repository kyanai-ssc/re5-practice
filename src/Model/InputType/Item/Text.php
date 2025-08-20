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
use Cake\Validation\Validator;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * Text class.
 */
class Text extends AbstractInputTypeItem implements
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

    public const TEXT_SEARCH_VALUE_MAX = 1000;

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
    public function getListValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $textValidator = new KuchenValidator();
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->addNested($this->getSearchInputKey(), $textValidator);

        $textValidator
            ->requirePresence('empty', false)
            ->allowEmptyString('empty')
            ->add('empty', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        $textValidator
            ->requirePresence('value', false)
            ->allowEmptyString('value')
            ->add('value', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::TEXT_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::TEXT_SEARCH_VALUE_MAX),
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

        return $this->applyInputTranslate($inputs, $this->getFieldsetColumn(), $this->getFormItemDetails(0));
    }

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        /** @var \Cake\Datasource\EntityInterface $formItemDetail */
        $formItemDetail = $this->getFormItemDetails(0);

        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();
        $minLength = 0;
        if ($formItemDetail->has('text_lower_limit')) {
            $minLength = $formItemDetail->get('text_lower_limit');
        }
        $maxLength = AbstractInputTypeManager::ITEM_DETAIL_TEXT_UPPER_LIMIT_MAX;
        if ($formItemDetail->has('text_upper_limit')) {
            $maxLength = $formItemDetail->get('text_upper_limit');
        }

        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getFieldsetColumn(), [
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

        $this->addInputCheckValidation($validator, $this->getFieldsetColumn(), $formItemDetail);

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
        return $this->formatAdditionCsvInputData($data);
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
}
