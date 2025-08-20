<?php
declare(strict_types=1);

namespace App\Controller\User\Ajax;

use App\Controller\Traits\AjaxTrait;
use App\Controller\UserAppController;
use App\Form\User\Auth\LoginForm;
use App\Locale\Message;
use Cake\Event\EventInterface;

/**
 * Auth Controller
 */
class AuthController extends UserAppController
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

        $this->Authentication->addUnauthenticatedActions([
            'login',
        ]);
        $this->FormProtection->setConfig('unlockedActions', [
            'login',
        ]);

        return $response;
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UserLoginHistoriesTable $userLoginHistoriesTable */
        $userLoginHistoriesTable = $this->fetchTable('UserLoginHistories');

        $finish = false;
        $loginForm = new LoginForm();

        $loginInputs = $this->getRequest()->getData();
        if ($loginForm->execute((array)$loginInputs)) {
            $identity = $this->Authentication->getIdentity();
            $login = $userLoginHistoriesTable->saveLoginLogs($identity, $loginForm->getData('login_id'));

            if ($login && !is_null($identity)) {
                if (!is_null($this->processLogin($identity))) {
                    $finish = true;
                }
            }
            if (!$finish) {
                $this->Flash->set((string)__(Message::LOGIN_FAILED), [
                    'key' => 'authError',
                    'element' => 'error',
                ]);
            }
        }

        if (!$finish && $this->commonData()->existsUserLoginData()) {
            $this->processLogout();
        }

        $this->set([
            'finish' => $finish,
            'loginForm' => $loginForm,
        ]);
    }
}
