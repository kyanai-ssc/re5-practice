<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\Admin\UsersController as BaseUsersController;
use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\Users\SearchForm;
use App\Form\Admin\Users\UpdateAuthorityForm;
use App\Form\Admin\Users\UploadForm;
use App\Form\Admin\Users\UserForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * Users Controller
 *
 * @property \App\Controller\Component\ImportComponent $Import
 */
class UsersController extends AdminAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('Import', [
            'model' => 'Users',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'searchForm',
            'import',
            'updateAuthority',
            'saveCheck',
            'changeForm',
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

        $selectUser = false;
        $selectUserQuery = [];
        if (is_scalar($this->getRequest()->getQuery('select_user'))) {
            $selectUser = true;
            $selectUserQuery = ['select_user' => Configure::readOrFail('Master.common.flg.on')];
        }

        $searchForm = new SearchForm();

        $this->set([
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'selectUser' => $selectUser,
            'selectUserQuery' => $selectUserQuery,
        ]);
    }

    /**
     * Import method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function import()
    {
        $this->Import->importData(new UploadForm(), false);
    }

    /**
     * UpdateAuthority method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function updateAuthority()
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $id = $this->getRequest()->getQuery('id');
        if (!$usersTable->validatePrimaryKey($id)) {
            throw new BadRequestException();
        }

        $user = $usersTable->get($id, [
            'finder' => 'updateAuthority',
        ]);

        $updateAuthorityForm = new UpdateAuthorityForm();
        $updateAuthorityForm->setEntity($user);

        $finish = false;
        if ($this->getRequest()->is('post')) {
            if ($updateAuthorityForm->execute((array)$this->getRequest()->getData())) {
                $usersTable->updateAuthority($user, (int)$updateAuthorityForm->getData('user_authority_id'));
                $finish = true;
            }
        } else {
            $this->setRequestData($updateAuthorityForm->getDefaultFieldValues());
        }

        $valueOptions = [];
        if (!$finish) {
            $valueOptions = $updateAuthorityForm->getFieldValueOptions();
        }

        $this->set([
            'finish' => $finish,
            'updateAuthorityForm' => $updateAuthorityForm,
            'valueOptions' => $valueOptions,
        ]);
    }

    /**
     * SaveCheck method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function saveCheck()
    {
        // チェック状態取得
        $checked = $this->SearchInput->getCheckedIds(
            $this->getRequest()->getSession()->read('users.list.search.checked')
        );
        if (!isset($checked['allCheck']) && count($checked) > Configure::readOrFail('Setting.listCheck.limit')) {
            throw new BadRequestException(Message::ERROR_LIST_CHECK_LIMIT);
        }

        // セッション保持
        $this->getRequest()->getSession()->write('users.list.search.checked', $checked);
    }

    /**
     * ChangeForm method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function changeForm()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $mode = $this->getRequest()->getQuery('mode');
        if (!in_array($mode, ['add', 'edit'], true)) {
            throw new BadRequestException();
        }

        $userForm = new UserForm();
        if ($mode === 'edit') {
            if (!$usersTable->validatePrimaryKey($this->getRequest()->getQuery('user_id'))) {
                throw new BadRequestException();
            }
            $userForm->setUserEntity($usersTable->get($this->getRequest()->getQuery('user_id'), [
                'finder' => 'edit',
            ]));
        }
        $userForm->setUserParameter($this->getRequest()->getQuery());
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException();
        }

        $data = $this->getRequest()->getData();
        unset($data['users']['password']);
        unset($data['users']['password_confirm']);
        $this->setRequestData($data);

        $userForm->initializeUserEntity($this->getRequest()->getData());

        $tokenKey = '';
        if ($mode === 'add') {
            $tokenKey = BaseUsersController::TOKEN_VALIDATION_ADD;
        } elseif ($mode === 'edit') {
            $userId = $userForm->getUserEntity()->get('id');
            $tokenKey = BaseUsersController::TOKEN_VALIDATION_EDIT . '_' . $userId;
        }
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
            'mode' => $mode,
        ]);
    }
}
