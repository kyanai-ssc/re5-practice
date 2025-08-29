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
            !$this->isAdmin() && isset($userId) && $formPatternDisplayType['id'] === FormItem::ID_ATTRIBUTE
        ) {
            $this->displayType['canInput'] = false;
        }
    }
}
