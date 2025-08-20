<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use Cake\Event\EventInterface;

/**
 * Labels Controller
 *
 * @property \App\Controller\Component\AjaxLabelComponent $AjaxLabel
 */
class LabelsController extends AdminAppController
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
            'public' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

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
