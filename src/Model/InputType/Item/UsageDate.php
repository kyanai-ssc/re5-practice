<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\ChargeTypeInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * UsageDate class.
 */
class UsageDate extends AbstractInputTypeItem implements ChargeTypeInterface, CsvInputInterface, CsvOutputInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;

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

        $usageTimestampFrom = DateTimeUtility::convertToDateObject($reservation->get('usage_timestamp_from'));
        if (!isset($usageTimestampFrom)) {
            return null;
        }

        return $usageTimestampFrom->format('Y/m/d');
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
        $result = [];
        if (isset($data)) {
            $data = DateTimeUtility::zeroPaddingDate($data);
        }
        $result['reservations']['usage_timestamp_from_date'] = $data;

        return $result;
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
        return Hash::get($errors, 'usage_timestamp_from_date', []);
    }
}
