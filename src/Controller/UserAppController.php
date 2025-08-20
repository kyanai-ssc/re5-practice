<?php
declare(strict_types=1);

namespace App\Controller;

use App\Locale\Message;
use App\Model\Entity\SiteSetting;
use Authentication\IdentityInterface;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Router;

/**
 * User Application Controller
 */
class UserAppController extends AppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->canAccess('user');

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        //基本設定で非公開の場合はNotFound
        if ($siteSettingsTable->getData()->get('front_public_flg') !== SiteSetting::FRONT_PUBLIC_FLG_ON) {
            throw new NotFoundException(Message::ERROR_NOT_PUBLIC);
        }

        $this->setTranslate(false);
        $this->setLabel($this->getRequest()->getSession()->read('common.label'));
        $this->setAuthUser($this->Authentication->getIdentity());
        $this->isAuthorizedAccess();

        $this->viewBuilder()->setLayout('user/default');
    }

    /**
     * ラベル情報を保持
     *
     * @param mixed $labelId ラベルID
     * @return void
     */
    protected function setLabel($labelId)
    {
        if (!is_null($labelId)) {
            $labelId = (int)$labelId;
            $this->getRequest()->getSession()->write('common.label', $labelId);
        }

        $this->commonData()->setUserLabelId($labelId);
    }

    /**
     * ラベル情報を初期化
     *
     * @return void
     */
    protected function resetLabel()
    {
        $this->getRequest()->getSession()->delete('common.label');
        $this->commonData()->setUserLabelId(null);
    }

    /**
     * 認証中の会員を設定
     *
     * @param \Authentication\IdentityInterface|null $identity 認証者の情報
     * @return \Cake\Http\Response|null|void
     */
    protected function setAuthUser(?IdentityInterface $identity)
    {
        if (!isset($identity)) {
            // 権限を設定
            $this->setAuthority();

            return;
        }

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        // ログイン中でログイン利用しない設定はログアウトを実施
        if (!$siteSettingsTable->getData()->isUseFlgOn('login_flg')) {
            $this->processLogout();

            return;
        }

        try {
            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $this->fetchTable('Users');

            $userData = $usersTable->get($identity->getIdentifier(), [
                'finder' => 'login',
            ]);

            $this->commonData()->setUserLoginData($userData);
        } catch (RecordNotFoundException $e) {
            $this->processLogout();

            return;
        }

        // 権限を設定
        $this->setAuthority();
    }

    /**
     * 権限を設定
     *
     * @return void
     */
    protected function setAuthority()
    {
        if ($this->commonData()->existsUserLoginData()) {
            $userAuthority = $this->commonData()->getUserLoginData()->get('user_authority');
        } else {
            /** @var \App\Model\Table\UserAuthoritiesTable $userAuthorityTable */
            $userAuthorityTable = $this->fetchTable('UserAuthorities');
            $userAuthority = $userAuthorityTable->getGuestAuthority();
        }

        // 権限をセット
        $this->commonData()->setUserAuthority($userAuthority);
        $this->Authority->setAuthority($userAuthority->get('access'));
    }

    /**
     * 会員権限チェック
     *
     * @return void|\Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException
     */
    protected function isAuthorizedAccess()
    {
        if ($this->getRequest()->is('ajax')) {
            return;
        }

        $controller = $this->getRequest()->getParam('controller');
        $action = $this->getRequest()->getParam('action');

        $authority = (array)Configure::read('Master.userAuthority.frontValue');
        $replace = (array)Configure::read('Master.userAuthority.replaceAuthority');

        if (!$this->Authority->checkAuthority($controller, $action, $authority, $replace)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
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
            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $this->fetchTable('Users');

            $userData = $usersTable->get($identity->getIdentifier(), [
                'finder' => 'login',
            ]);

            $this->commonData()->setUserLoginData($userData);
        } catch (RecordNotFoundException $e) {
            return $this->processLogout();
        }

        // 権限を設定
        $this->setAuthority();

        $redirectUrl = $this->redirectToParam();
        if (!is_null($redirectUrl)) {
            return $redirectUrl;
        }

        return $this->getAuthRedirectUrl();
    }

    /**
     * 未ログイン画面の表示判定
     *
     * @param array $action 許可する画面
     * @return void
     */
    protected function isLoginRequire($action)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $siteSetting = $siteSettingsTable->getData();

        //ログイン必須でない場合は画面許可
        if ($siteSetting->get('login_use_flg') !== SiteSetting::LOGIN_USE_FLG_REQUIRE) {
            $this->Authentication->addUnauthenticatedActions(array_map('strval', $action));
        }
    }

    /**
     * 基本設定にて利用可能かどうか
     *
     * @param string $flg フラグ名
     * @return \Cake\Http\Exception\NotFoundException|void
     */
    protected function canUseAction($flg)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn($flg)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * ログアウト処理を実行
     *
     * @return string リダイレクト先URL
     */
    protected function processLogout()
    {
        $labelId = $this->commonData()->getUserLabelId();
        $sessionConfig = $this->getRequest()->getSession()->read('Config');

        $redirectUrl = $this->Authentication->logout();
        $this->getRequest()->getSession()->clear();
        if (!is_null($sessionConfig)) {
            $this->getRequest()->getSession()->write('Config', $sessionConfig);
        }
        $this->commonData()->removeUserLoginData();

        // ラベルIDは再度セットする
        $this->setLabel($labelId);

        // 権限を設定
        $this->setAuthority();

        if (is_null($redirectUrl)) {
            $redirectUrl = Router::url([
                'controller' => 'Auth',
                'action' => 'login',
            ]);
        }

        return $redirectUrl;
    }

    /**
     * ログイン済みの場合TOPへリダイレクト
     *
     * @return \Cake\Http\Response|null|void
     */
    protected function alreadyLoggedRedirect()
    {
        if ($this->commonData()->existsUserLoginData()) {
            return $this->redirect($this->getTopUrl());
        }
    }

    /**
     * Authの値とパラメータIDの値のチェック（一致しない場合はNotFound）
     *
     * @param int|null $id user.id
     * @return void
     */
    protected function checkUserId($id)
    {
        if (is_null($id)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        /** @var \Authentication\Identity|null $identity */
        $identity = $this->Authentication->getIdentity();
        if (empty($identity) || (string)$identity->offsetGet('id') !== (string)$id) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * ログイン後の画面復帰を有効化
     *
     * @return void
     */
    protected function enableLoginRedirectBack()
    {
        $this->viewBuilder()->addHelper('Login', [
            'backOnLogin' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getAuthRedirectUrlDefault()
    {
        if (
            !$this->Authority->checkAuthority(
                'Index',
                'index',
                (array)Configure::read('Master.userAuthority.frontValue'),
                (array)Configure::read('Master.userAuthority.replaceAuthority')
            )
        ) {
            return Router::url([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'calendar',
            ]);
        }

        return Router::url($this->getTopUrl());
    }

    /**
     * TOPのURLを取得
     *
     * @return array
     */
    protected function getTopUrl()
    {
        $url = [
            'prefix' => 'User',
            'controller' => 'Index',
            'action' => 'index',
        ];
        $label = $this->commonData()->getUserLabelId();
        if (isset($label)) {
            $url['?'] = [
                'label' => $label,
            ];
        }

        return $url;
    }
}
