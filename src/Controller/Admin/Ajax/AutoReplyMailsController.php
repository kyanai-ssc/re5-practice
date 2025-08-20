<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\AutoReplyMails\TestMail;
use App\Mailer\DefaultMailer;
use Cake\Event\EventInterface;

/**
 * AutoReplyMails Controller
 */
class AutoReplyMailsController extends AdminAppController
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
            'testMail',
        ]);

        return $response;
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

        // 入力値取得
        $testMailInputs = $this->getRequest()->getData();

        // 入力チェック
        if ($testMailForm->execute((array)$testMailInputs)) {
            // セッション保持
            $mail = new DefaultMailer();
            $testMail = $this->fetchTable('AutoReplyMails')->newEntity($testMailInputs, ['validate' => false]);
            $mail->sendAutoReplyMailTest($testMail, $testMailForm->getData('test_mail'));

            $checkResult = true;
        }

        $this->set('checkResult', $checkResult);
        $this->set('errors', $testMailForm->getErrors());
        $this->set('formName', $testMailForm->getFormName());
    }
}
