<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\OptinTrait;
use App\Controller\Traits\UsersTrait;
use App\Controller\UserAppController;
use App\Form\User\User\OptinForm;
use App\Form\User\User\PasswordForm;
use App\Form\User\User\UserForm;
use App\Locale\Message;
use App\Model\Entity\OptinToken;
use App\Model\Entity\RecaptchaSetting;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * User Controller
 */
class UserController extends UserAppController
{
    use OptinTrait;
    use UsersTrait;

    public const TOKEN_VALIDATION_ADD = 'users_add';
    public const TOKEN_VALIDATION_EDIT = 'users_edit';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'mailAdd',
            'mailAddFinish',
            'token',
            'add',
            'addConf',
            'addFinish',
            'withdrawFinish',
        ]);

        return $response;
    }

    /**
     * Mail method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailAdd()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $result = $this->mailAddAction(OptinToken::TYPE_USER);
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'mail-add-finish',
            ]);
        }
    }

    /**
     * Mail finish method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailAddFinish()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $this->mailAddFinishAction();
    }

    /**
     * token method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function token()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $optinForm = new OptinForm();
        $this->tokenAction($optinForm);
        if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            $this->getRequest()->getSession()->write('userOptin', $optinForm->getOptinData());
        }

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'User',
            'action' => 'add',
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $optinData = [];
        if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            $optinForm = new OptinForm();
            if (!$this->getRequest()->getSession()->check('userOptin')) {
                if (!$this->getRequest()->is('post')) {
                    return $this->redirect([
                        'prefix' => 'User',
                        'controller' => 'User',
                        'action' => 'mail-add',
                    ]);
                }
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            if (!$optinForm->execute((array)$this->getRequest()->getSession()->read('userOptin.optinToken'))) {
                $this->getRequest()->getSession()->delete('userOptin');
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $optinData = (array)$this->getRequest()->getSession()->read('userOptin.optinData');
        }

        $userForm = new UserForm();
        $userForm->setOptinData($optinData);

        $result = $this->addAction($userForm, static::TOKEN_VALIDATION_ADD, false);
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'add-conf',
            ]);
        }
    }

    /**
     * Add conf method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function addConf()
    {
        // reCATPTCHA
        if ($this->getRequest()->is('post')) {
            $this->Recaptcha->verify(RecaptchaSetting::ACTION_USER);
        }

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $optinData = [];
        $optinToken = null;
        if ($siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            $optinForm = new OptinForm();
            if (!$this->getRequest()->getSession()->check('userOptin')) {
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            if (!$optinForm->execute((array)$this->getRequest()->getSession()->read('userOptin.optinToken'))) {
                $this->getRequest()->getSession()->delete('userOptin');
                throw new BadRequestException(Message::ERROR_INVALID_URL);
            }
            $optinData = (array)$this->getRequest()->getSession()->read('userOptin.optinData');
            $optinToken = $optinForm->getOptinToken();
        }

        $userForm = new UserForm();
        $userForm->setOptinData($optinData);

        $result = $this->addConfAction(
            $userForm,
            static::TOKEN_VALIDATION_ADD,
            Configure::readOrFail('Master.common.flg.on'),
            $optinToken
        );
        if ($result) {
            $this->getRequest()->getSession()->delete('userOptin');

            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'add-finish',
                'id' => $userForm->getUserEntity()->get('id'),
            ]);
        }
    }

    /**
     * Add finish method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function addFinish($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_add_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->addFinishAction($id);
    }

    /**
     * Detail method
     *
     * @param int $id id
     * @return \Cake\Http\Response|null|void
     */
    public function detail($id = null)
    {
        $this->checkUserId($id);

        $userForm = new UserForm();

        $this->viewAction($this->commonData()->getUserLoginData()->get('id'), $userForm);
    }

    /**
     * Edit method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $this->checkUserId($id);

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_edit_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $userForm = new UserForm();

        $result = $this->editAction(
            $this->commonData()->getUserLoginData()->get('id'),
            $userForm,
            static::TOKEN_VALIDATION_EDIT,
            false
        );
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'edit-conf',
                'id' => $this->commonData()->getUserLoginData()->get('id'),
            ]);
        }
    }

    /**
     * Edit conf method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function editConf($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $this->checkUserId($id);

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_edit_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $userForm = new UserForm();

        $result = $this->editConfAction(
            $id,
            $userForm,
            static::TOKEN_VALIDATION_EDIT,
            Configure::readOrFail('Master.common.flg.on')
        );
        if ($result) {
            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'User',
                'action' => 'edit-finish',
                'id' => $userForm->getUserEntity()->get('id'),
            ]);
        }
    }

    /**
     * Edit finish method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function editFinish($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $this->checkUserId($id);

        if (!$siteSettingsTable->getData()->isUseFlgOn('user_edit_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $this->editFinishAction($id);
    }

    /**
     * passwordEdit method password変更
     *
     * @param int|null $id id
     * @return \Cake\Http\Response|null|void
     */
    public function passwordEdit($id = null)
    {
        $this->checkUserId($id);

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $user = $usersTable->get($this->commonData()->getUserLoginData()->get('id'), [
            'finder' => 'edit',
        ]);

        $identity = $this->Authentication->getIdentity();
        $passwordForm = new PasswordForm();
        if (isset($identity)) {
            /** @var \Authentication\Identity $identity */
            $passwordForm->setUserId($identity->offsetGet('id'));
        }
        if ($this->getRequest()->is('post')) {
            if ($passwordForm->execute((array)$this->getRequest()->getData())) {
                if ($usersTable->updatePassword($user, $passwordForm->getData('password'))) {
                    return $this->redirect([
                        'prefix' => 'User',
                        'controller' => 'User',
                        'action' => 'passwordEditFinish',
                        'id' => $this->commonData()->getUserLoginData()->get('id'),
                    ]);
                }
            }
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'passwordEditErrors',
                'element' => 'error',
            ]);
        }

        // ビュー変数
        $this->set([
            'passwordForm' => $passwordForm,
            'user' => $user,
        ]);
    }

    /**
     * passwordEditFinish method パスワード変更完了
     *
     * @param int|null $id id
     * @return \Cake\Http\Response|null|void
     */
    public function passwordEditFinish($id = null)
    {
        $this->checkUserId($id);
    }

    /**
     * withdraw method 退会申請
     *
     * @param int $id id
     * @return \Cake\Http\Response|null|void
     */
    public function withdraw($id)
    {
        $this->checkUserId($id);

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $user = $usersTable->get($this->commonData()->getUserLoginData()->get('id'), [
            'finder' => 'edit',
        ]);

        if (!$user->canWithdraw()) {
            throw new BadRequestException(Message::ERROR_CANNOT_WITHDRAW);
        }

        if ($this->getRequest()->is('post')) {
            // 退会処理
            $saveOptions = [
                'mailSendFlg' => Configure::readOrFail('Master.common.flg.on'),
            ];
            if ($usersTable->withdraw($user, $saveOptions)) {
                $userId = $this->commonData()->getUserLoginData()->get('id');
                $this->getRequest()->getSession()->write('withdraw.finish', $userId);

                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'withdrawFinish',
                    'id' => $userId,
                ]);
            }
            $this->Flash->set((string)__(Message::ERROR_CANNOT_WITHDRAW), [
                'key' => 'withdrawErrors',
                'element' => 'error',
            ]);
        }

        // ビュー変数
        $this->set([
            'user' => $user,
        ]);
    }

    /**
     * withdrawFinish method 退会申請完了
     *
     * @param int $id id
     * @return \Cake\Http\Response|null|void
     */
    public function withdrawFinish($id)
    {
        $logoutUserId = $this->getRequest()->getSession()->read('withdraw.finish');
        $this->processLogout();

        if (!is_scalar($logoutUserId) || (string)$logoutUserId !== (string)$id) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * mailEdit method メールアドレス変更入力
     *
     * @param int|null $id id
     * @return \Cake\Http\Response|null|void
     */
    public function mailEdit($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\OptinTokensTable $optinTokensTable */
        $optinTokensTable = $this->fetchTable('OptinTokens');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $this->checkUserId($id);

        if (!$siteSettingsTable->getData()->isUseFlgOn('mail_edit_optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $user = $usersTable->get($this->commonData()->getUserLoginData()->get('id'), [
            'finder' => 'edit',
        ]);

        if ($this->getRequest()->is('post')) {
            $optinToken = $optinTokensTable->newEntity($this->getRequest()->getData());
            $optinToken->set('type', OptinToken::TYPE_MAIL_EDIT);
            $optinToken->set('user_id', $this->commonData()->getUserLoginData()->get('id'));
            if ($optinTokensTable->save($optinToken)) {
                return $this->redirect([
                    'prefix' => 'User',
                    'controller' => 'User',
                    'action' => 'mailEditFinish',
                    'id' => $this->commonData()->getUserLoginData()->get('id'),
                ]);
            }
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'mailEditErrors',
                'element' => 'error',
            ]);
        } else {
            $optinToken = $optinTokensTable->newEntity([], ['validate' => false]);
        }

        // ビュー変数
        $this->set([
            'optinToken' => $optinToken,
            'user' => $user,
        ]);
    }

    /**
     * mailEditFinish method メールアドレス変更入力完了
     *
     * @param int|null $id id
     * @return \Cake\Http\Response|null|void
     */
    public function mailEditFinish($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $this->checkUserId($id);

        if (!$siteSettingsTable->getData()->isUseFlgOn('mail_edit_optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $user = $usersTable->get($this->commonData()->getUserLoginData()->get('id'), [
            'finder' => 'edit',
        ]);

        // ビュー変数
        $this->set([
            'user' => $user,
        ]);
    }

    /**
     * mailEditApproval method メールアドレス変更認証
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailEditApproval()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        if (!$siteSettingsTable->getData()->isUseFlgOn('mail_edit_optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if (!$this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $optinForm = new OptinForm();
        if (!$optinForm->execute((array)$this->getRequest()->getQuery())) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }

        $user = $usersTable->get($this->commonData()->getUserLoginData()->get('id'), [
            'finder' => 'edit',
        ]);

        // メールアドレスを更新
        if (
            !($optinForm->getOptinToken() instanceof OptinToken)
            || !$usersTable->updateMail($user, $optinForm->getOptinToken())
        ) {
            throw new CakeException();
        }

        return $this->redirect([
            'prefix' => 'User',
            'controller' => 'User',
            'action' => 'mailEditApprovalFinish',
        ]);
    }

    /**
     * mailEditApprovalFinish method メールアドレス変更認証完了
     *
     * @return \Cake\Http\Response|null|void
     */
    public function mailEditApprovalFinish()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('mail_edit_optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if (!$this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
    }
}
