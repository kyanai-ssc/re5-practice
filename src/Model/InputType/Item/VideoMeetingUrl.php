<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\VideoMeetingTrait;
use Cake\Utility\Hash;

/**
 * VideoMeetingUrl class.
 */
class VideoMeetingUrl extends AbstractInputTypeItem implements CsvOutputInterface, MailOutputInterface
{
    use CsvOutputTrait;
    use MailOutputTrait;
    use VideoMeetingTrait;

    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        if (!$this->hasVideoMeeting()) {
            $this->displayType['canDisplay'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $videoMeeting = $this->getReservationVideoMeeting(Hash::get((array)$options, 'reservation'));
        if (!isset($videoMeeting)) {
            return null;
        }

        return $videoMeeting['video_meeting_url'];
    }

    /**
     * @inheritDoc
     */
    public function canCsvOutput()
    {
        return $this->canUseVideoMeeting();
    }

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
    public function getMailReplaceToken()
    {
        return 'video_meeting_url';
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return $this->getDetailValue($data);
    }
}
