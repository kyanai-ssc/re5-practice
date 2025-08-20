<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Utility\DateTimeUtility;
use Cake\Utility\Hash;

/**
 * EventScheduleDate class.
 */
class EventScheduleDate extends AbstractInputTypeItem implements MailOutputInterface
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

        $value = $this->getOutputText($event->get('date_from'), $event->get('date_to'));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_schedule_date';
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

        $value = $this->getOutputText($event['date_from'], $event['date_to']);

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param string|\DateTimeInterface|null $dateFrom 開始日
     * @param string|\DateTimeInterface|null $dateTo 終了日
     * @return string|null 文字列
     */
    protected function getOutputText($dateFrom, $dateTo)
    {
        $format = 'Y/m/d';

        $dateFrom = DateTimeUtility::convertToDateObject($dateFrom);
        $dateTo = DateTimeUtility::convertToDateObject($dateTo);

        $text = null;
        if (isset($dateFrom) && isset($dateTo)) {
            if ($dateFrom->format('Y-m-d') === $dateTo->format('Y-m-d')) {
                $text = $dateFrom->format($format);
            } else {
                $text = $dateFrom->format($format) . ' ～ ' . $dateTo->format($format);
            }
        } else {
            if (isset($dateFrom)) {
                $text = $dateFrom->format($format) . ' ～';
            }
            if (isset($dateTo)) {
                $text = '～ ' . $dateTo->format($format);
            }
        }

        return $text;
    }
}
