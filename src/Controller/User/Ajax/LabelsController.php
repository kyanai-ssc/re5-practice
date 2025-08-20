<?php
declare(strict_types=1);

namespace App\Controller\User\Ajax;

use App\Controller\Traits\AjaxTrait;
use App\Controller\UserAppController;
use Cake\Event\EventInterface;

/**
 * Labels Controller
 *
 * @property \App\Controller\Component\AjaxLabelComponent $AjaxLabel
 */
class LabelsController extends UserAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('AjaxLabel', [
            'public' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'parent',
        ]);
        $this->FormProtection->setConfig('unlockedActions', [
            'parent',
        ]);

        return $response;
    }

    /**
     * Parent method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function parent()
    {
        $this->AjaxLabel->parent();
    }
}
