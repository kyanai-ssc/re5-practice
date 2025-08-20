<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use Cake\View\CellTrait;

/**
 * Cms Controller
 */
class CmsController extends AdminAppController
{
    use CellTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Cms',
            'action' => 'edit',
        ], 301);
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $cms = $siteSettingsTable->get($siteSettingsTable->getId($id), [
            'finder' => 'cms',
        ]);

        $cms->setCmsAccess();
        if ($this->getRequest()->is('post')) {
            $siteSettingsTable->patchEntity($cms, (array)$this->getRequest()->getData(), [
                'validate' => 'Cms',
            ]);

            if (
                $siteSettingsTable->save($cms, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'save' => 'cms',
                'cell' => $this->cell('Css'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'cmsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Cms',
                    'action' => 'edit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'cmsErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'cms' => $cms,
            'siteThemeError' => $cms->getError('site_theme'),
            'valueOptions' => $siteSettingsTable->getFieldValueOptions(),
        ]);
    }
}
