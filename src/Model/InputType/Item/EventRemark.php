<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Mailer\DefaultMailer;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Utility\StringUtility;
use Cake\Utility\Hash;

/**
 * EventRemark class.
 */
class EventRemark extends AbstractInputTypeItem implements MailOutputInterface
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
        $eventRemarks = $event->get('event_remarks');
        if (!isset($eventRemarks)) {
            return null;
        }

        $value = null;
        foreach ((array)$eventRemarks as $eventRemark) {
            if ((string)$eventRemark->get('form_item_id') === ((string)$this->getFormItem()->get('id'))) {
                $value = $eventRemark->get('remark');
                break;
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return 'event_remark_' . $this->getFormItem()->get('id');
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        $eventRemarks = Hash::get($data, 'event.event_remarks', []);

        $contentType = DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT;
        if (is_array($options)) {
            $contentType = Hash::get($options, 'content_type', DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT);
        }

        $value = null;
        foreach ($eventRemarks as $eventRemark) {
            if ((string)Hash::get($eventRemark, 'form_item_id') === ((string)$this->getFormItem()->get('id'))) {
                if ($contentType === DefaultMailer::MAIL_FORMAT_CONTENTS_HTML) {
                    $value = Hash::get($eventRemark, 'remark');
                } else {
                    $value = StringUtility::nl2brStripTags(Hash::get($eventRemark, 'remark', ''));
                }

                break;
            }
        }

        return $value;
    }

    /**
     * メールの出力内容をエスケープ
     *
     * @return bool 判定結果
     */
    public function useEscapeValue()
    {
        return false;
    }
}
