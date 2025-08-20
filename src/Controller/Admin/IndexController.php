<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;

/**
 * Index Controller
 */
class IndexController extends AdminAppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\AppSettingsTable $appSettingsTable */
        $appSettingsTable = $this->fetchTable('AppSettings');
        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        if ($loginData->isOperatorAdmin()) {
            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Reservations',
                'action' => 'calendar',
            ]);
        }

        $this->set([
            'userCount' => $this->fetchTable('Users')->find('planRestriction')->count(),
            'mailCount' => $this->fetchTable('MailDeliveryHistories')->find('sendCount')->count(),
            'systemSetting' => $systemSettingsTable->getData(),
            'accessSummaries' => $this->fetchTable('AccessSummaries')->find('access')->first(),
            'appSetting' => $appSettingsTable->getAppSetting(),
            'restrictionMail' => $mailDeliveriesTable->getRestrictionMail(),
        ]);
    }
}
