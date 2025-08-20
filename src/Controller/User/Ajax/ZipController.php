<?php
declare(strict_types=1);

namespace App\Controller\User\Ajax;

use App\Controller\Traits\AjaxTrait;
use App\Controller\Traits\AjaxZipTrait;
use App\Controller\UserAppController;
use App\Form\User\Zip\SearchForm;
use Cake\Event\EventInterface;

/**
 * Zip Controller
 */
class ZipController extends UserAppController
{
    use AjaxTrait;
    use AjaxZipTrait;

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
            'search',
        ]);
        $this->FormProtection->setConfig('unlockedActions', [
            'search',
        ]);

        return $response;
    }

    /**
     * Search method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function search()
    {
        $searchForm = new SearchForm();

        $this->searchAction($searchForm);
    }
}
