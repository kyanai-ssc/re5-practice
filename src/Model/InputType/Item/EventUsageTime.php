<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\Event;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use Cake\Utility\Hash;

/**
 * EventUsageTime class.
 */
class EventUsageTime extends AbstractInputTypeItem implements MailOutputInterface
{
    use MailOutputTrait;

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

        $value = $this->getOutputText(
            $event->get('type'),
            $event->get('time_plan'),
            $event->get('usage_time_from'),
            $event->get('usage_time_to'),
            $event->get('usage_day_from'),
            $event->get('usage_day_to')
        );

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_usage_time';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $event = Hash::get($data, 'event');
        if (!isset($event)) {
            return null;
        }

        $value = $this->getOutputText(
            $event['type'],
            $event['time_plan'],
            $event['usage_time_from'],
            $event['usage_time_to'],
            $event['usage_day_from'],
            $event['usage_day_to']
        );

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param int $type タイプ
     * @param int $timePlan プラン
     * @param int|null $usageTimeFrom 受付時間数From
     * @param int|null $usageTimeTo 受付時間数To
     * @param int|null $usageDayFrom 受付日数From
     * @param int|null $usageDayTo 受付日数To
     * @return string|null 文字列
     */
    protected function getOutputText($type, $timePlan, $usageTimeFrom, $usageTimeTo, $usageDayFrom, $usageDayTo)
    {
        if (((string)$timePlan) !== ((string)Event::PLAN_SINGLE)) {
            return null;
        }

        $minuteText = __('reservation/dateTimeMinute');
        $dayText = __('reservation/dateTimeDay');
        $textForSame = [
            Event::TYPE_TIME => '%USAGE_TIME_FROM%' . $minuteText,
            Event::TYPE_DAY => '%USAGE_DAY_FROM%' . $dayText,
        ];
        $textForNotSame = [
            Event::TYPE_TIME => '%USAGE_TIME_FROM%' . $minuteText . ' ～ %USAGE_TIME_TO%' . $minuteText,
            Event::TYPE_DAY => '%USAGE_DAY_FROM%' . $dayText . ' ～ %USAGE_DAY_TO%' . $dayText,
        ];

        $targetValue = [
            Event::TYPE_TIME => [
                'from' => $usageTimeFrom,
                'to' => $usageTimeTo,
            ],
            Event::TYPE_DAY => [
                'from' => $usageDayFrom,
                'to' => $usageDayTo,
            ],
        ];

        $text = null;
        if (((string)$targetValue[$type]['from']) === ((string)$targetValue[$type]['to'])) {
            $text = $textForSame;
        } else {
            $text = $textForNotSame;
        }

        $pattern = [
            '/%USAGE_TIME_FROM%/',
            '/%USAGE_TIME_TO%/',
            '/%USAGE_DAY_FROM%/',
            '/%USAGE_DAY_TO%/',
        ];
        $replacement = [
            (string)$usageTimeFrom,
            (string)$usageTimeTo,
            (string)$usageDayFrom,
            (string)$usageDayTo,
        ];

        return preg_replace($pattern, $replacement, $text[$type]);
    }
}
