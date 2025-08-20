<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * JSON非同期通信コンポーネント
 */
class AjaxComponent extends Component
{
    /**
     * Component startup.
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function startup(EventInterface $event)
    {
        if (!$this->getController()->getRequest()->is('ajax')) {
            throw new NotFoundException();
        }
    }
}
