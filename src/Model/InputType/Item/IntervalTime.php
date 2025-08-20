<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\Event;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use Cake\Utility\Hash;

/**
 * IntervalTime class.
 */
class IntervalTime extends AbstractInputTypeItem implements MailOutputInterface
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

        $value = $this->getOutputText($event->get('type'), $event->get('interval_time'), $event->get('interval_day'));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_interval_time';
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

        $value = $this->getOutputText($event['type'], $event['interval_time'], $event['interval_day']);

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param int $type タイプ
     * @param int|null $time 時間数
     * @param int|null $day 日数
     * @return string|null 文字列
     */
    protected function getOutputText($type, $time, $day)
    {
        if (((string)$time) === '' && ((string)$day) === '') {
            return null;
        }

        $text = [
            Event::TYPE_TIME => '%INTERVAL_TIME%' . __('reservation/dateTimeMinute'),
            Event::TYPE_DAY => '%INTERVAL_DAY%' . __('reservation/dateTimeDay'),
        ];

        $result = preg_replace(['/%INTERVAL_TIME%/', '/%INTERVAL_DAY%/'], [(string)$time, (string)$day], $text[$type]);

        return $result;
    }
}
