<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Auth\LoginForm;
use App\Locale\Message;
use Cake\Event\EventInterface;

/**
 * Auth Controller
 */
class AuthController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'index',
            'login',
            'logout',
        ]);

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
            'controller' => 'Auth',
            'action' => 'login',
        ]);
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $loginForm = new LoginForm();

        if ($this->getRequest()->is('post')) {
            $loginInputs = (array)$this->getRequest()->getData();
            if ($loginForm->execute($loginInputs)) {
                /** @var \App\Model\Table\AdminLoginHistoriesTable $adminLoginHistoriesTable */
                $adminLoginHistoriesTable = $this->fetchTable('AdminLoginHistories');

                // 認証者情報の取得
                $identity = $this->Authentication->getIdentity();
                $login = $adminLoginHistoriesTable->saveLoginLogs($identity, $loginForm->getData('login_id'));

                if ($login && !is_null($identity)) {
                    $redirect = $this->processLogin($identity);
                    if (isset($redirect)) {
                        return $this->redirect($redirect);
                    }
                }
                if ($this->commonData()->existsAdminLoginData()) {
                    $this->processLogout();
                }

                $this->Flash->set((string)__(Message::LOGIN_FAILED), [
                    'key' => 'authErrors',
                    'element' => 'error',
                ]);
            } else {
                if ($this->commonData()->existsAdminLoginData()) {
                    $this->processLogout();
                }
            }
        } else {
            if ($this->commonData()->existsAdminLoginData()) {
                return $this->redirect($this->getAuthRedirectUrl());
            }
        }

        $this->set([
            'loginForm' => $loginForm,
        ]);
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function logout()
    {
        return $this->redirect($this->processLogout());
    }
}
