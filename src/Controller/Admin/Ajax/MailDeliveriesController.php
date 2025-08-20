<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\MailDeliveries\SearchForm;
use App\Form\Admin\MailDeliveries\TestMail;
use App\Locale\Message;
use App\Mailer\DefaultMailer;
use Cake\Event\EventInterface;

/**
 * MailDeliveries Controller
 */
class MailDeliveriesController extends AdminAppController
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
            'searchForm',
            'testMail',
        ]);

        return $response;
    }

    /**
     * SearchForm method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function searchForm()
    {
        $this->getRequest()->allowMethod('post');

        $searchForm = new SearchForm();

        $this->set([
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * 一覧のチェック情報を保持
     *
     * @return \Cake\Http\Response|null|void
     */
    public function testMail()
    {
        $checkResult = false;
        $testMailForm = new TestMail();

        $mailDeliveryInputs = $this->getRequest()->getSession()->read('mailDeliveries.add.inputs');
        if (!empty($mailDeliveryInputs)) {
            $searchInputs = (array)$this->getRequest()->getData();

            // 入力チェック
            if ($testMailForm->execute($searchInputs)) {
                // セッション保持
                $mail = new DefaultMailer();
                $mailDelivery = $this->fetchTable('MailDeliveries')->newEntity($mailDeliveryInputs, [
                    'validate' => false,
                ]);
                $success = $mail->mailDelivery(
                    $mailDelivery,
                    $this->fetchTable('Users')->newEntity([], ['validate' => false]),
                    $testMailForm->getData('mail')
                );

                if (!$success) {
                    $checkResult = false;
                    $errorMessage[] = __(Message::FAILED_TEST_MAIL);
                } else {
                    $checkResult = true;
                }
            } else {
                $checkResult = false;
                $errorMessage[] = __(Message::ERROR_MAIL_ADDRESS);
            }
        } else {
            $checkResult = false;
            $errorMessage[] = __(Message::FAILED_TEST_MAIL);
        }

        $this->set('checkResult', $checkResult);
        $this->set('errors', $testMailForm->getErrors());
    }
}
