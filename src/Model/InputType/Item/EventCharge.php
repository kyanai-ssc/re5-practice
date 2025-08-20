<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\Event;
use App\Model\Entity\EventPlan;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * EventCharge class.
 */
class EventCharge extends AbstractInputTypeItem implements MailOutputInterface
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
            $event->get('event_unit_time'),
            $event->get('charge'),
            $event->get('event_plans')
        );

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_charge';
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
            $event['event_unit_time'],
            $event['charge'],
            Hash::get($event, 'event_plans')
        );

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param int $type タイプ
     * @param int $timePlan プラン
     * @param int $eventUnitTime 単位時間
     * @param int|null $charge 料金
     * @param array|null $eventPlans 予約枠プラン
     * @return string 文字列
     */
    protected function getOutputText($type, $timePlan, $eventUnitTime, $charge, $eventPlans)
    {
        $minuteText = __('reservation/dateTimeMinute');
        $dayText = __('reservation/dateTimeDay');
        $chargeText = __('reservation/chargeUnit');
        $textForSingle = [
            Event::TYPE_TIME => '%EVENT_UNIT_TIME%' . $minuteText . ' %CHARGE%' . $chargeText,
            Event::TYPE_DAY => '1' . $dayText . ' %CHARGE%' . $chargeText,
        ];
        $textForMultiple = [
            Event::TYPE_TIME => '%PLAN_NAME% %PLAN_USAGE_TIME%' . $minuteText . ' %PLAN_CHARGE%' . $chargeText,
            Event::TYPE_DAY => '%PLAN_NAME% %PLAN_USAGE_DAY%' . $dayText . ' %PLAN_CHARGE%' . $chargeText,
        ];
        $multipleSeparator = "\n";

        $text = null;
        if (((string)$timePlan) === ((string)Event::PLAN_SINGLE)) {
            $pattern = [
                '/%EVENT_UNIT_TIME%/',
                '/%CHARGE%/',
            ];
            $replacement = [
                $eventUnitTime,
                (string)$charge,
            ];
            $text = preg_replace($pattern, $replacement, $textForSingle[$type]);
        }
        if (((string)$timePlan) === ((string)Event::PLAN_MULTIPLE)) {
            $planText = [];
            foreach ((array)$eventPlans as $eventPlan) {
                if ($this->isAdmin() || (string)$eventPlan['public_flg'] === (string)EventPlan::PUBLIC_FLG_ON) {
                    $pattern = [
                        '/%PLAN_NAME%/',
                        '/%PLAN_USAGE_TIME%/',
                        '/%PLAN_USAGE_DAY%/',
                        '/%PLAN_CHARGE%/',
                    ];
                    $replacement = [
                        $eventPlan['name'],
                        $eventPlan['usage_time'],
                        $eventPlan['usage_day'],
                        $eventPlan['charge'],
                    ];
                    $planText[] = preg_replace($pattern, $replacement, $textForMultiple[$type]);
                }
            }
            $text = implode($multipleSeparator, $planText);
        }
        if (!is_string($text)) {
            throw new CakeException();
        }

        return $text;
    }
}
