<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\Event;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use Cake\Utility\Hash;

/**
 * EditingDeadline class.
 */
class EditingDeadline extends AbstractInputTypeItem implements MailOutputInterface
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
            $event->get('editing_deadline_type'),
            $event->get('editing_deadline_number'),
            $event->get('editing_deadline_time'),
            $event->isEditingDeadlineCriterionTo()
        );

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_editing_deadline';
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
            $event['editing_deadline_type'],
            $event['editing_deadline_number'],
            $event['editing_deadline_time'],
            (string)$event['editing_deadline_criterion'] === (string)Event::CRITERION_TO
        );

        return $value;
    }

    /**
     * 表示する文字列を取得
     *
     * @param int $type タイプ
     * @param int $number 時間数
     * @param string|null $time 時間
     * @param bool $isCriterionTo 判定基準を利用終了日時とするか
     * @return string 文字列
     */
    protected function getOutputText($type, $number, $time, $isCriterionTo)
    {
        $text = [
            Event::DEADLINE_TYPE_TIME => $isCriterionTo ? (string)__('reservation/deadlineHourTo', $number) :
                (string)__('reservation/deadlineHour', $number),
            Event::DEADLINE_TYPE_DAY => $isCriterionTo ? (string)__('reservation/deadlineDayTo', $number, $time) :
                (string)__('reservation/deadlineDay', $number, $time),
        ];

        return $text[$type];
    }
}
