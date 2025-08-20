<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use Cake\Event\EventInterface;

/**
 * Events Controller
 */
class EventsController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->isLoginRequire([
            'view',
        ]);

        return $response;
    }

    /**
     * View method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $this->Frame->allow();
        $eventFrame = false;
        if (is_scalar($this->getRequest()->getQuery('frame'))) {
            $eventFrame = true;
        }

        $event = $this->fetchTable('Events')->get($id, [
            'finder' => 'forUser',
        ]);

        $this->enableLoginRedirectBack();
        $this->set([
            'event' => $event,
            'eventFrame' => $eventFrame,
        ]);
    }
}
