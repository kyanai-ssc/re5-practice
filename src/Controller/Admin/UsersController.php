<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Controller\Traits\UsersTrait;
use App\Form\Admin\Users\DeleteManyForm;
use App\Form\Admin\Users\SearchForm;
use App\Form\Admin\Users\UserForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * Users Controller
 */
class UsersController extends AdminAppController
{
    use UsersTrait;

    public const TOKEN_VALIDATION_ADD = 'users_add';
    public const TOKEN_VALIDATION_EDIT = 'users_edit';
    public const TOKEN_VALIDATION_DELETE_MANY = 'users_delete_many';

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'withdraw',
            'delete',
            'download',
            'sample',
            'approval',
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
            'controller' => 'Users',
            'action' => 'list',
        ], 301);
    }

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        // 会員選択ボタン判定
        $sessionKey = 'users.list.search';
        $selectUser = false;
        $selectUserQuery = [];
        if (is_scalar($this->getRequest()->getQuery('select_user'))) {
            $sessionKey = 'users.list.select';
            $selectUser = true;
            $selectUserQuery = ['select_user' => Configure::readOrFail('Master.common.flg.on')];
        }

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete($sessionKey);
        } elseif ($this->SearchInput->checkSearchButtonClick()) {
            $this->getRequest()->getSession()->delete($sessionKey . '.checked');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read($sessionKey . '.data')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read($sessionKey . '.data')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $users = $this->Pagination->paginate($this->fetchTable('Users'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write($sessionKey . '.data', $searchData);

        // ビュー変数
        $this->set([
            'users' => $users,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'check' => $this->SearchInput->getListCheckInfo(
                $searchForm->getFieldValueOptions(),
                $this->getRequest()->getSession()->read($sessionKey . '.checked')
            ),
            'selectUser' => $selectUser,
            'selectUserQuery' => $selectUserQuery,
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        $userForm = new UserForm();

        $result = $this->addAction($userForm, static::TOKEN_VALIDATION_ADD, true);
        if ($result) {
            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Users',
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
        $userForm = new UserForm();

        $result = $this->addConfAction(
            $userForm,
            static::TOKEN_VALIDATION_ADD,
            $this->getRequest()->getData('mail_check'),
            null,
            $this->getRequest()->getAttribute('params')
        );
        if ($result) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                'key' => 'usersFinish',
                'element' => 'success',
            ]);

            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Users',
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
        $this->addFinishAction($id);
    }

    /**
     * View method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function view($id = null)
    {
        $userForm = new UserForm();

        $this->viewAction($id, $userForm);
    }

    /**
     * Edit method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        $userForm = new UserForm();

        $result = $this->editAction($id, $userForm, static::TOKEN_VALIDATION_EDIT, true);
        if ($result) {
            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Users',
                'action' => 'edit-conf',
                'id' => $id,
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
        $userForm = new UserForm();

        $result = $this->editConfAction(
            $id,
            $userForm,
            static::TOKEN_VALIDATION_EDIT,
            $this->getRequest()->getData('mail_check'),
            $this->getRequest()->getAttribute('params')
        );
        if ($result) {
            // 完了メッセージ
            $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                'key' => 'usersFinish',
                'element' => 'success',
            ]);

            return $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Users',
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
        $this->editFinishAction($id);
    }

    /**
     * Withdraw method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function withdraw($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $user = $usersTable->get($id, [
            'finder' => 'withdraw',
        ]);

        $saveOptions = [
            'mailSendFlg' => $this->getRequest()->getData('mail_check'),
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ];
        if (!$usersTable->withdraw($user, $saveOptions)) {
            $errors = $user->getErrors();
            if (!empty($errors)) {
                $errors = Hash::flatten($errors);
                throw new BadRequestException(reset($errors));
            } else {
                throw new BadRequestException();
            }
        }
        $this->Flash->set((string)__(Message::WITHDRAW_SUCCESS), [
            'key' => 'usersFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * Delete method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $user = $usersTable->get($id, [
            'finder' => 'delete',
        ]);

        if (!$user->canDelete()) {
            throw new NotFoundException();
        }

        $deleteOptions = [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ];
        if (!$usersTable->delete($user, $deleteOptions)) {
            $errors = $user->getErrors();
            if (!empty($errors)) {
                $errors = Hash::flatten($errors);
                throw new BadRequestException(reset($errors));
            } else {
                throw new BadRequestException();
            }
        }
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'usersFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * DeleteMany method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function deleteMany()
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $checked = $this->getRequest()->getSession()->read('users.list.search.checked');
        $condition = $this->getRequest()->getSession()->read('users.list.search.data');
        if (!is_array($checked) || empty($checked) || !isset($condition)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (!is_array($condition)) {
            $condition = [];
        }

        $selectInfo = [
            'checked' => $checked,
            'condition' => $condition,
        ];

        $users = $usersTable->find('checkUsers', $selectInfo);

        $deleteManyForm = new DeleteManyForm();
        $deleteManyForm->setDeleteChecked($selectInfo);

        if ($this->getRequest()->is('post')) {
            $deleteManyInputs = (array)$this->getRequest()->getData();
            if ($deleteManyForm->execute($deleteManyInputs)) {
                if (
                    $usersTable->deleteUsers(
                        $selectInfo['checked'],
                        $selectInfo['condition'],
                        $this->getRequest()->getAttribute('params')
                    )
                ) {
                    $this->getRequest()->getSession()->delete('users.list.search.checked');

                    $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
                        'key' => 'usersFinish',
                        'element' => 'success',
                    ]);

                    return $this->redirect([
                        'prefix' => 'Admin',
                        'controller' => 'Users',
                        'action' => 'list',
                        '?' => Configure::read('Setting.searchInput.searchQuery'),
                    ]);
                } else {
                    throw new BadRequestException(Message::DELETE_MANY_ERROR);
                }
            } else {
                throw new BadRequestException(Message::DELETE_MANY_ERROR);
            }
        }

        // ビュー変数
        $this->set([
            'users' => $users,
            'checked' => json_encode($selectInfo),
            'deleteManyForm' => $deleteManyForm,
        ]);
    }

    /**
     * Download method チェックしたデータのダウンロード
     *
     * @return \Cake\Http\Response|null|void
     */
    public function downloadChecked()
    {
        set_time_limit(180);

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $checked = $this->getRequest()->getSession()->read('users.list.search.checked');
        $condition = $this->getRequest()->getSession()->read('users.list.search.data');
        if (!is_array($checked) || empty($checked) || !isset($condition)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        }

        if (!is_array($condition)) {
            $condition = [];
        }

        $fileName = Configure::readOrFail('Setting.csv.download.user.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $callback = $usersTable->createCsvCheck($checked, $condition);

        return $this->FileDownload->setStreamDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.user.type'),
            $callback
        );
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function download()
    {
        set_time_limit(180);
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        /** @var array|null $searchData */
        $searchData = $this->getRequest()->getSession()->read('users.list.search.data');

        if (!isset($searchData)) {
            throw new BadRequestException();
        }

        $fileName = Configure::readOrFail('Setting.csv.download.user.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $callback = $usersTable->createCsv($searchData);

        return $this->FileDownload->setStreamDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.user.type'),
            $callback
        );
    }

    /**
     * Sample method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sample()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $fileName = Configure::readOrFail('Setting.csv.import.user.sample.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $filePath = $usersTable->createSampleCsv();

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.import.user.sample.type'),
            $filePath,
            true
        );
    }

    /**
     * approval method
     *
     * @param int $id 会員ID
     * @return \Cake\Http\Response|null|void
     */
    public function approval($id = null)//TODO
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        $user = $usersTable->get($id, [
            'finder' => 'edit',
        ]);

        // 顧客に登録されている属性を取得
        $additionValues = $user->get('addition_values');
        $attributeId = $additionValues[Configure::read('Setting.formItemAdditionValues.attribute')]; //属性の番号

        /** @var \App\Model\Table\FormItemChoicesTable $formItemChoicesTable */
        $formItemChoicesTable = $this->fetchTable('FormItemChoices');

        $attribute = $formItemChoicesTable->get($attributeId);
        $attributeName = $attribute->name;

        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');

        // 顧客に登録されている属性と同じ名前の権限名の顧客の権限データを取得
        $userAuthority = $userAuthoritiesTable->getSameNameAuthority($attributeName);

        if ($userAuthority == null) {
            $this->Flash->set((string)__(Message::NO_EXIST_ATTRIBUTE_AUTHORITY_NAME), [
            'key' => 'usersErrors',
            'element' => 'errors',
            ]);
        }

        // 取得した権限を会員の権限に書き換える
        $usersTable->updateAuthority($user, (int)$userAuthority['id']);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Users',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
