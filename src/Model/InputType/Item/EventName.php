<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * EventName class.
 */
class EventName extends AbstractInputTypeItem implements
    CsvInputInterface,
    CsvOutputInterface,
    ListOutputInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    public const EVENT_NAME_SEARCH_VALUE_MAX = 1000;

    /**
     * @var string
     */
    protected $tableName = 'events';

    /**
     * @var string
     */
    protected $columnName = 'name';

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }

        return $event->get('name');
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
        return 'Events.sort_no';
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
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::EVENT_NAME_SEARCH_VALUE_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::EVENT_NAME_SEARCH_VALUE_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $value = Hash::get($inputs, $this->getSearchInputKey());
        if (isset($value) && $value !== '') {
            $parameter = $this->driverExpression()->escapeLike($value);
            $query->where([
                $this->getTableAlias() . '.' . $this->getColumnName() . ' LIKE' => '%' . $parameter . '%',
            ]);
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }

        return $this->csvFormat()->csvForId($event->get('id'), $event->get('name'));
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        $result['reservations']['event_id'] = $this->csvFormat()->inputForId($data);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.reservationEventId');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'event_id', []);
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_name';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'event.name');
    }
}
