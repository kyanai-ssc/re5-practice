<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Payment Controller
 */
class PaymentController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');

        if (!$systemSettingsTable->getData()->usePayment()) {
            throw new NotFoundException();
        }

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Payment',
            'action' => 'view',
        ], 301);
    }

    /**
     * View method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function view()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->fetchTable('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();

        if (!$paymentSetting) {
            throw new NotFoundException();
        }
        $this->set([
            'paymentSetting' => $paymentSetting,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->fetchTable('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();
        if (!isset($paymentSetting) || !$paymentSetting->canEdit()) {
            throw new NotFoundException();
        }

        if ($this->getRequest()->is('post')) {
            $paymentSettingsTable->patchEntity($paymentSetting, (array)$this->getRequest()->getData());
            if (
                $paymentSettingsTable->save($paymentSetting, [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'paymentSettingFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Payment',
                    'action' => 'view',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'paymentSettingErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'paymentSetting' => $paymentSetting,
        ]);
    }
}
