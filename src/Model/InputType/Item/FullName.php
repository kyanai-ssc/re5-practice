<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use Cake\Core\Configure;

/**
 * FullName class.
 */
class FullName extends MultiTextbox
{
    /**
     * @var string
     */
    protected $delimiter = '　';

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
        $values = preg_split('/' . preg_quote($this->delimiter, '/') . '/u', (string)$data, $this->getFieldCount());
        if (!is_array($values)) {
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
        return Configure::readOrFail('Setting.csv.import.sample.fullName');
    }

    /**
     * @inheritDoc
     */
    protected function replaceForbiddenString($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        $result = preg_replace('/' . preg_quote($this->delimiter, '/') . '/u', ' ', $data);

        return $result;
    }
}
