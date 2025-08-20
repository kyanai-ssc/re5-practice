<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\Event;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\ChargeTypeInterface;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\CsvInputTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * UsageTime class.
 */
class UsageTime extends AbstractInputTypeItem implements ChargeTypeInterface, CsvInputInterface, CsvOutputInterface
{
    use CsvInputTrait;
    use CsvOutputTrait;

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $text = [
            Event::TYPE_TIME => '%USAGE_TIME_FROM% ～',
            Event::TYPE_DAY => '%TIME_FROM% ～ %TIME_TO%',
        ];
        $format = 'H:i';

        if (is_null($options)) {
            return null;
        }

        $event = Hash::get($options, 'event');
        if (!isset($event)) {
            return null;
        }
        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        $usageTimestampFrom = DateTimeUtility::convertToDateTimeObject($reservation->get('usage_timestamp_from'));
        if (!isset($usageTimestampFrom)) {
            return null;
        }

        $timeFrom = DateTimeUtility::convertToTimeObject($event->get('time_from'));
        $timeTo = DateTimeUtility::convertToTimeObject($event->get('time_to'));
        if (!isset($timeFrom) || !isset($timeTo)) {
            throw new CakeException();
        }

        $pattern = [
            '/%USAGE_TIME_FROM%/',
            '/%TIME_FROM%/',
            '/%TIME_TO%/',
        ];
        $replacement = [
            $usageTimestampFrom->format($format),
            $timeFrom->format($format),
            $timeTo->format($format),
        ];

        return preg_replace($pattern, $replacement, $text[$event->get('type')]);
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        if (is_null($options)) {
            return null;
        }

        $reservation = Hash::get($options, 'reservation');
        if (!isset($reservation)) {
            return null;
        }

        return $this->csvFormat()->csvForTime($reservation->get('usage_timestamp_from'));
    }

    /**
     * @inheritDoc
     */
    public function formatCsvInputData(?string $data = null)
    {
        $result = [];
        if (isset($data)) {
            $data = DateTimeUtility::zeroPaddingTime($data);
        }
        $result['reservations']['usage_timestamp_from_time'] = $data;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getCsvDescription(?array $options = null)
    {
        return Configure::readOrFail('Setting.csv.import.sample.time');
    }

    /**
     * @inheritDoc
     */
    protected function getCsvErrorMessages(array $errors): array
    {
        return Hash::get($errors, 'usage_timestamp_from_time', []);
    }
}
