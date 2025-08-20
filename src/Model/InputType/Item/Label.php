<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\ListOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Label class.
 */
class Label extends AbstractInputTypeItem implements
    CsvOutputInterface,
    ListOutputInterface,
    MailOutputInterface,
    SearchDisplayInterface
{
    use CsvOutputTrait;
    use ListOutputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    /**
     * @var string
     */
    protected $tableName = 'events';

    /**
     * @var string
     */
    protected $columnName = 'label_id';

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

        $label = $event->get('label');
        if (!isset($label)) {
            return null;
        }

        return $label->get('name');
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
        return 'Events.label_id';
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
            ->allowEmptyString($this->getSearchInputKey());

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator, $this->getSearchInputKey());

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $value = Hash::get($inputs, $this->getSearchInputKey());
        if (is_scalar($value) && ((string)$value !== '')) {
            /** @var \App\Model\Table\LabelsTable $labelsTable */
            $labelsTable = $this->getTableLocator()->get('Labels');

            $labelsTable->addNestWhere($query, (int)$value);
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

        $label = $event->get('label');
        if (!isset($label)) {
            return null;
        }

        return $this->csvFormat()->csvForId($label->get('id'), $label->get('name'));
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_label';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return Hash::get($data, 'event.label.name');
    }
}
