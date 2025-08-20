<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\FormPatternDisplayType;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\VideoMeetingTrait;
use Cake\Utility\Hash;

/**
 * VideoMeetingOrganizer class.
 */
class VideoMeetingOrganizer extends AbstractInputTypeItem implements CsvOutputInterface
{
    use CsvOutputTrait;
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

        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        $organizers = $organizersTable->getOrganizerList();
        $organizerId = $videoMeeting['organizer_id'];
        if (!isset($organizers[$organizerId])) {
            return null;
        }

        return $organizers[$organizerId]['name'];
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
        $videoMeeting = $this->getReservationVideoMeeting(Hash::get((array)$options, 'reservation'));
        if (!isset($videoMeeting)) {
            return null;
        }

        /** @var \App\Model\Table\OrganizersTable $organizersTable */
        $organizersTable = $this->getTableLocator()->get('Organizers');

        $organizers = $organizersTable->getOrganizerList();
        $organizerId = $videoMeeting['organizer_id'];
        if (!isset($organizers[$organizerId])) {
            return null;
        }

        return $this->csvFormat()->csvForId($organizerId, $organizers[$organizerId]['name']);
    }
}
