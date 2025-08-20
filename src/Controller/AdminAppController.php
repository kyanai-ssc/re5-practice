<?php
declare(strict_types=1);

namespace App\Controller;

use App\Http\ServerRequest;
use App\Model\Entity\Admin;
use Authentication\IdentityInterface;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Client;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Router;
use Cake\Validation\Validation;
use Exception;

/**
 * Admin Application Controller
 */
class AdminAppController extends AppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->canAccess('admin');

        $this->setTranslate(true);
        $this->setAuthAdmin($this->Authentication->getIdentity());
        $this->isAuthorizedAccess();

        $this->viewBuilder()->setLayout('admin/default');
    }

    /**
     * 認証中の管理者を設定
     *
     * @param \Authentication\IdentityInterface|null $identity 認証者の情報
     * @return void
     */
    protected function setAuthAdmin(?IdentityInterface $identity)
    {
        if (!isset($identity)) {
            return;
        }

        try {
            /** @var \App\Model\Table\AdminsTable $adminsTable */
            $adminsTable = $this->fetchTable('Admins');

            $adminData = $adminsTable->get($identity->getIdentifier(), [
                'finder' => 'login',
            ]);

            $this->commonData()->setAdminLoginData($adminData);
        } catch (RecordNotFoundException $e) {
            $this->processLogout();

            return;
        }

        /** @var \App\Model\Entity\Admin $admin */
        $admin = $this->commonData()->getAdminLoginData();

        if ($admin->isSystemAdmin()) {
            $serverRequest = new ServerRequest();
            $ipAddress = Configure::read('Env.systemAdmin.ipAddress');
            if (!empty($ipAddress) && !$serverRequest->canAccessIp($ipAddress)) {
                $this->processLogout();

                return;
            }
        }

        // 権限を設定
        $this->setAuthority();

        $redirectUrl = Router::url([
            'prefix' => 'Admin',
            'controller' => 'Admins',
            'action' => 'edit',
            'id' => $admin->get('id'),
        ]);
        $labelUrl = Router::url([
            'prefix' => 'Admin/Ajax',
            'controller' => 'Labels',
            'action' => 'parent',
        ]);

        if (
            $admin->isPassReset() === Admin::FIRST_TIME_RESET
            && $this->getRequest()->getRequestTarget() != $redirectUrl
            && $this->getRequest()->getRequestTarget() != $labelUrl
            && $this->getRequest()->getRequestTarget() != Router::url([
                'controller' => 'Auth',
                'action' => 'logout',
            ])
        ) {
            $this->redirect($redirectUrl);
        }
    }

    /**
     * 権限を設定
     *
     * @return void
     */
    protected function setAuthority()
    {
        if (!$this->commonData()->existsAdminLoginData()) {
            return;
        }

        /** @var \App\Model\Entity\Admin $admin */
        $admin = $this->commonData()->getAdminLoginData();

        $this->Authority->setAuthority(Configure::readOrFail('Master.admin.authorityAcl.' . $admin->get('authority')));

        if ($admin->isSystemAdmin()) {
            $this->Authority->setRejectedAuthority(Configure::readOrFail('Master.admin.systemAdminAcl'));
        }
        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->fetchTable('AdminAuthorities');
        // 利用許可画面のパターン設定の設定値をセット
        $data = $adminAuthoritiesTable->getAccessData($admin->get('admin_authority'));
        $this->Authority->setAuthority($data);
    }

    /**
     * 権限チェック
     *
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException
     */
    protected function isAuthorizedAccess()
    {
        if (!$this->commonData()->existsAdminLoginData()) {
            return;
        }

        if ($this->getRequest()->is('ajax')) {
            return;
        }

        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');

        if (!$this->Authority->checkAuthority($controller, $action)) {
            throw new NotFoundException();
        }
    }

    /**
     * ログイン処理を実行
     *
     * @param \Authentication\IdentityInterface $identity ログインユーザーの情報
     * @return string|null リダイレクト先URL
     */
    protected function processLogin(IdentityInterface $identity)
    {
        try {
            /** @var \App\Model\Table\AdminsTable $adminsTable */
            $adminsTable = $this->fetchTable('Admins');

            $adminData = $adminsTable->get($identity->getIdentifier(), [
                'finder' => 'login',
            ]);

            $this->commonData()->setAdminLoginData($adminData);
        } catch (RecordNotFoundException $e) {
            return $this->processLogout();
        }

        /** @var \App\Model\Entity\Admin $admin */
        $admin = $this->commonData()->getAdminLoginData();

        if ($admin->isSystemAdmin()) {
            $serverRequest = new ServerRequest();
            $ipAddress = Configure::read('Env.systemAdmin.ipAddress');
            if (!empty($ipAddress) && !$serverRequest->canAccessIp($ipAddress)) {
                return $this->processLogout();
            }
        }

        // 権限を設定
        $this->setAuthority();

        if ($admin->isPassReset() === Admin::FIRST_TIME_RESET || $admin->isPassReset() === Admin::LIMIT_TIME_RESET) {
            // 管理者編集画面に遷移させる
            return Router::url([
                'prefix' => 'Admin',
                'controller' => 'Admins',
                'action' => 'edit',
                'id' => $identity->getIdentifier(),
            ]);
        }

        $redirectUrl = $this->redirectToParam();
        if (!is_null($redirectUrl)) {
            return $redirectUrl;
        }

        return $this->getAuthRedirectUrl();
    }

    /**
     * ログアウト処理を実行
     *
     * @return string リダイレクト先URL
     */
    protected function processLogout()
    {
        $sessionConfig = $this->getRequest()->getSession()->read('Config');

        $redirectUrl = $this->Authentication->logout();
        $this->getRequest()->getSession()->clear();
        if (!is_null($sessionConfig)) {
            $this->getRequest()->getSession()->write('Config', $sessionConfig);
        }
        $this->commonData()->removeAdminLoginData();

        if (is_null($redirectUrl)) {
            $redirectUrl = Router::url(['controller' => 'Auth', 'action' => 'login']);
        }

        return $redirectUrl;
    }

    /**
     * お知らせ表示
     *
     * @return array|null お知らせ
     */
    protected function getAnnounce()
    {
        $url = Configure::readOrFail('Env.announce.url');
        try {
            if (Validation::url($url)) {
                $http = new Client();
                $response = $http->post($url, [
                    'secret_key' => Configure::readOrFail('Env.announce.apiKey'),
                    'client' => Configure::readOrFail('Client.host'),
                ]);

                if ($response->isOk()) {
                    $data = $response->getJson();

                    return $data;
                }
            }
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getAuthRedirectUrlDefault()
    {
        if ($this->commonData()->existsAdminLoginData()) {
            $admin = $this->commonData()->getAdminLoginData();
            if (
                $admin->get('authority') === Admin::AUTHORITY_OPERATOR
                || $admin->get('authority') === Admin::AUTHORITY_REGULAR
            ) {
                if (!$this->Authority->checkAuthority('Reservations', 'calendar')) {
                    return Router::url([
                        'prefix' => 'Admin',
                        'controller' => 'Admins',
                        'action' => 'list',
                    ]);
                } else {
                    return Router::url([
                        'prefix' => 'Admin',
                        'controller' => 'Reservations',
                        'action' => 'calendar',
                    ]);
                }
            }
        }

        return Router::url([
            'prefix' => 'Admin',
            'controller' => 'Index',
            'action' => 'index',
        ]);
    }

    /**
     * 決済期限切れ絞り込み対象か判定
     *
     * @return bool
     */
    protected function isSearchPaymentExpired()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        $paymentSetting = $paymentSettingsTable->getData();

        if (
            $systemSettingsTable->getData()->usePayment()
            && isset($paymentSetting)
            && $paymentSetting->isPaymentServiceSb()
            && is_scalar($this->getRequest()->getQuery('search_payment_expired'))
        ) {
            return true;
        }

        return false;
    }

    /**
     * 未連携一覧画面として画面を開いているか判定
     *
     * @return bool
     */
    protected function isSearchReservationUnlinkedSmartLock()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');

        if (
            $systemSettingsTable->getData()->useSmartLock()
            && is_scalar($this->getRequest()->getQuery('search_smart_lock_unlinked'))
        ) {
            return true;
        }

        return false;
    }
}
