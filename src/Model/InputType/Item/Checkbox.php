<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Checkbox class.
 */
class Checkbox extends Radio
{
    /**
     * @var string
     */
    protected $columnType = 'json';

    /**
     * @inheritDoc
     */
    public function valueToDatabase($value)
    {
        $value = array_values(Hash::map($value, '{*}', function ($data) {
            return (int)$data;
        }));

        return parent::valueToDatabase($value);
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

        $valueWhere = $this->buildAdditionArraySearchQuery($query, $inputs);
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
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY), !$isRequired)
            ->add($this->getFieldsetColumn(), [
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
    public function formatCsvInputData(?string $data = null)
    {
        $inputs = $this->csvFormat()->inputsForMultiple($data);
        if (empty($inputs)) {
            return null;
        }

        $values = [];
        foreach ($inputs as $key => $value) {
            $values[$key] = $this->csvFormat()->inputForId($value);
        }

        return $this->formatAdditionCsvInputData($values);
    }
}
