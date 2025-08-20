<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

/**
 * Prefecture class.
 */
class Prefecture extends Select
{
    /**
     * @inheritDoc
     */
    public function getValueOptions()
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        return $prefecturesTable->getValueOptions();
    }
}
