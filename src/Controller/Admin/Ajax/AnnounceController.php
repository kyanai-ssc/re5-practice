<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use Cake\Event\EventInterface;

/**
 * Announce Controller
 */
class AnnounceController extends AdminAppController
{
    use AjaxTrait;

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

        $this->FormProtection->setConfig('unlockedActions', [
            'index',
        ]);

        return $response;
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        $this->set([
            'announce' => $this->getAnnounce(),
        ]);
    }
}
