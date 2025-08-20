<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * EventScheduleTime class.
 */
class EventScheduleTime extends AbstractInputTypeItem implements MailOutputInterface
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

        $value = $this->getOutputText($event->get('time_from'), $event->get('time_to'));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_schedule_time';
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

        $value = $this->getOutputText($event['time_from'], $event['time_to']);

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param string|\DateTimeInterface $timeFrom 開始日
     * @param string|\DateTimeInterface $timeTo 終了日
     * @return string 文字列
     */
    protected function getOutputText($timeFrom, $timeTo)
    {
        $text = '%TIME_FROM% ～ %TIME_TO%';
        $format = 'H:i';

        $timeFrom = DateTimeUtility::convertToTimeObject($timeFrom);
        $timeTo = DateTimeUtility::convertToTimeObject($timeTo);
        if (!isset($timeFrom) || !isset($timeTo)) {
            throw new CakeException();
        }

        $pattern = [
            '/%TIME_FROM%/',
            '/%TIME_TO%/',
        ];
        $replacement = [
            $timeFrom->format($format),
            $timeTo->format($format),
        ];

        $result = preg_replace($pattern, $replacement, $text);
        if (!is_string($result)) {
            throw new CakeException();
        }

        return $result;
    }
}
