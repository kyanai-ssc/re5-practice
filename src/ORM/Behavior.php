<?php
declare(strict_types=1);

namespace App\ORM;

use Cake\ORM\Behavior as CakeBehavior;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Class Behavior
 */
class Behavior extends CakeBehavior
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        $events = parent::implementedEvents();

        $eventMap = [
            'Model.afterMarshal' => [
                'callable' => 'afterMarshal',
                'priority' => 100,
            ],
            'Model.beforeSaveMany' => [
                'callable' => 'beforeSaveMany',
                'priority' => 100,
            ],
            'Model.afterSaveMany' => [
                'callable' => 'afterSaveMany',
                'priority' => 100,
            ],
            'Model.afterSaveManyCommit' => [
                'callable' => 'afterSaveManyCommit',
                'priority' => 100,
            ],
            'Model.afterUpdateMany' => [
                'callable' => 'afterUpdateMany',
                'priority' => 100,
            ],
            'Model.beforeDeleteData' => [
                'callable' => 'beforeDeleteData',
                'priority' => 100,
            ],
        ];
        foreach ($eventMap as $event => $options) {
            if (method_exists($this, $options['callable'])) {
                $events[$event] = $options;
            }
        }

        return $events;
    }
}
