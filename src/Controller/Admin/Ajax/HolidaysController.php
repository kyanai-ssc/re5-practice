<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\Holidays\UploadForm;
use Cake\Event\EventInterface;

/**
 * Holidays Controller
 *
 * @property \App\Controller\Component\ImportComponent $Import
 */
class HolidaysController extends AdminAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('Import', [
            'model' => 'Holidays',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'import',
        ]);

        return $response;
    }

    /**
     * Import method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function import()
    {
        $this->Import->importData(new UploadForm(), false);
    }
}
