<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;

/**
 * System Controller
 */
class SiteSettingController extends AdminAppController
{
    /**
     * Edit method
     *
     * @param int|null $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $siteSetting = $siteSettingsTable->get($siteSettingsTable->getId($id), [
            'finder' => 'system',
        ]);

        $siteSetting->setSystemAccess();
        if ($this->getRequest()->is('post')) {
            $siteSettingsTable->patchEntity($siteSetting, (array)$this->getRequest()->getData(), [
                'validate' => 'siteSetting',
            ]);

            if (
                $siteSettingsTable->save($siteSetting, [
                'save' => 'SiteSetting',
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'siteSettingFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'SiteSetting',
                    'action' => 'edit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'siteSettingErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set('siteSetting', $siteSetting);
        $this->set('valueOptions', $siteSettingsTable->getFieldValueOptions());
    }
}
