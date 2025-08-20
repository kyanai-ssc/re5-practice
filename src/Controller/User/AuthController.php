<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Form\User\Auth\LoginForm;
use App\Locale\Message;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Auth Controller
 */
class AuthController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        if (!$siteSettingsTable->getData()->isUseFlgOn('login_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

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
            'prefix' => 'User',
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
                /** @var \App\Model\Table\UserLoginHistoriesTable $userLoginHistoriesTable */
                $userLoginHistoriesTable = $this->fetchTable('UserLoginHistories');

                // 認証者情報の取得
                $identity = $this->Authentication->getIdentity();
                $login = $userLoginHistoriesTable->saveLoginLogs($identity, $loginForm->getData('login_id'));

                if ($login && !is_null($identity)) {
                    $redirect = $this->processLogin($identity);
                    if (isset($redirect)) {
                        return $this->redirect($redirect);
                    }
                }
                if ($this->commonData()->existsUserLoginData()) {
                    $this->processLogout();
                }

                $this->Flash->set((string)__(Message::LOGIN_FAILED), [
                    'key' => 'authErrors',
                    'element' => 'error',
                ]);
            } else {
                if ($this->commonData()->existsUserLoginData()) {
                    $this->processLogout();
                }
            }
        } else {
            if ($this->commonData()->existsUserLoginData()) {
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
