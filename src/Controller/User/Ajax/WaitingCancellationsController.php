<?php
declare(strict_types=1);

namespace App\Controller\User\Ajax;

use App\Controller\Traits\AjaxTrait;
use App\Controller\Traits\WaitingCancellationsTrait;
use App\Controller\UserAppController;
use Cake\Event\EventInterface;

/**
 * WaitingCancellations Controller
 */
class WaitingCancellationsController extends UserAppController
{
    use AjaxTrait;
    use WaitingCancellationsTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'add',
        ]);
        $this->FormProtection->setConfig('unlockedActions', [
            'add',
        ]);

        return $response;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $this->addAction();
    }
}
