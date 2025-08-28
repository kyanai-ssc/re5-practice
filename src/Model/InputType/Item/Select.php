<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Model\Entity\FormItem;
use App\Model\Entity\FormPatternDisplayType;

/**
 * Select class.
 */
class Select extends Radio
{
    /**
     * @inheritDoc
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        parent::settingDisplayType($formPatternDisplayType);

        $userId = $this->getConfig('userId');

        if (
            !$this->isAdmin() && isset($userId) && $formPatternDisplayType['id'] === formItem::FORM_ITEMS_ID_ATTRIBUTE
        ) {
            $this->displayType['canInput'] = false;
        }
    }
}
