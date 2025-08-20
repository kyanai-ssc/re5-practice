<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Locale\Message;
use Cake\Http\Exception\BadRequestException;

/**
 * Users trait.
 */
trait UsersTrait
{
    /**
     * Add action
     *
     * @param \App\Form\Common\Users\UserForm $userForm 会員フォーム
     * @param string $tokenKey トークンキー
     * @param bool $useQueryParameter クエリの利用有無
     * @return bool
     */
    protected function addAction($userForm, $tokenKey, $useQueryParameter)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // プラン制限チェック
        if (!$usersTable->checkPlanRestriction()) {
            throw new BadRequestException(Message::PLAN_USER_RESTRICTION_OVER);
        }

        // 会員パラメータチェック
        if ($this->getRequest()->getQuery('input') === 'back') {
            if (!$this->getRequest()->getSession()->check('users.add.parameter')) {
                throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
            }
            $userForm->setUserParameter($this->getRequest()->getSession()->read('users.add.parameter'));
        } else {
            if ($useQueryParameter && is_array($this->getRequest()->getQuery())) {
                $userForm->setUserParameter($this->getRequest()->getQuery());
            } else {
                $userForm->setUserParameter([]);
            }
        }
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $userInputs = $this->getRequest()->getData();
            if ($userForm->execute((array)$userInputs)) {
                if ($this->TokenValidation->validate($tokenKey)) {
                    $this->getRequest()->getSession()->write('users.add', [
                        'parameter' => $userForm->getUserParameter(),
                        'data' => $userForm->getData(),
                        'crypt' => $userForm->getUserEntity()->get('crypt_password'),
                    ]);

                    return true;
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'usersError',
                    'element' => 'error',
                ]);
            }
            $this->setRequestData($userForm->getData());
        } else {
            // エンティティ初期化
            $userInputs = [];
            if ($this->getRequest()->getQuery('input') === 'back') {
                if (!$this->getRequest()->getSession()->check('users.add.data')) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }
                $userInputs = $this->getRequest()->getSession()->read('users.add.data');
            }
            $userForm->initializeUserEntity($userInputs);
        }

        // セッション削除
        $this->getRequest()->getSession()->delete('users.add');

        // トークン生成
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Add conf action
     *
     * @param \App\Form\Common\Users\UserForm $userForm 会員フォーム
     * @param string $tokenKey トークンキー
     * @param mixed $mailSendFlg メール送信フラグ
     * @param \App\Model\Entity\OptinToken|null $optinToken オプトイントークン
     * @param array|null $saveOperation 操作ログ
     * @return bool
     */
    protected function addConfAction($userForm, $tokenKey, $mailSendFlg, $optinToken = null, $saveOperation = null)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // プラン制限チェック
        if (!$usersTable->checkPlanRestriction()) {
            throw new BadRequestException(Message::PLAN_USER_RESTRICTION_OVER);
        }

        // セッションチェック
        if (!$this->getRequest()->getSession()->check('users.add')) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 会員パラメータチェック
        $userForm->setConfirm(true);
        $userForm->setUserParameter($this->getRequest()->getSession()->read('users.add.parameter'));
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // データ登録
            $userInputs = $this->getRequest()->getSession()->read('users.add.data');
            if ($userForm->execute((array)$userInputs)) {
                if ($this->TokenValidation->validate($tokenKey)) {
                    $saveOptions = [
                        'mailSendFlg' => $mailSendFlg,
                        'cryptPassword' => $this->getRequest()->getSession()->read('users.add.crypt'),
                        'optinToken' => $optinToken,
                        'saveOperation' => $saveOperation,
                    ];
                    if ($usersTable->save($userForm->getUserEntity(), $saveOptions)) {
                        $this->getRequest()->getSession()->delete('users.add');

                        return true;
                    }
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'usersError',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ初期化
            $userForm->initializeUserEntity($this->getRequest()->getSession()->read('users.add.data'));
        }

        // トークン生成
        $this->TokenValidation->generate($tokenKey);

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Add finish action
     *
     * @param int|null $id 会員ID
     * @return void
     */
    protected function addFinishAction($id)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        if (!$usersTable->validatePrimaryKey($id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'userId' => $id,
        ]);
    }

    /**
     * View action
     *
     * @param int|null $id 予約ID
     * @param \App\Form\Common\Users\UserForm $userForm 会員フォーム
     * @return void
     */
    protected function viewAction($id, $userForm)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // データ取得
        $userForm->setUserEntity($usersTable->get($id, [
            'finder' => 'edit',
        ]));

        // 会員パラメータチェック
        $userForm->setUserParameter([]);
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Edit action
     *
     * @param int|null $id 会員ID
     * @param \App\Form\Common\Users\UserForm $userForm 会員フォーム
     * @param string $tokenKey トークンキー
     * @param bool $useQueryParameter クエリの利用有無
     * @return bool
     */
    protected function editAction($id, $userForm, $tokenKey, $useQueryParameter)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // データ取得
        $userForm->setUserEntity($usersTable->get($id, [
            'finder' => 'edit',
        ]));
        if (!$userForm->getUserEntity()->canEdit()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 会員パラメータチェック
        if ($useQueryParameter) {
            if ($this->getRequest()->is('post') || $this->getRequest()->getQuery('input') !== 'back') {
                $userForm->setUserParameter($this->getRequest()->getQuery());
            } else {
                if (!$this->getRequest()->getSession()->check('users.edit.' . $id)) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }
                $userForm->setUserParameter(
                    $this->getRequest()->getSession()->read('users.edit.' . $id . '.parameter')
                );
            }
        } else {
            $userForm->setUserParameter([]);
        }
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // 入力チェック
            $userInputs = $this->getRequest()->getData();
            if ($userForm->execute((array)$userInputs)) {
                if ($this->TokenValidation->validate($tokenKey . '_' . $id)) {
                    $this->getRequest()->getSession()->write('users.edit.' . $id, [
                        'parameter' => $userForm->getUserParameter(),
                        'data' => $userForm->getData(),
                        'crypt' => $userForm->getUserEntity()->get('crypt_password'),
                    ]);

                    return true;
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'usersError',
                    'element' => 'error',
                ]);
            }
            $this->setRequestData($userForm->getData());
        } else {
            // エンティティ初期化
            $userInputs = [];
            if ($this->getRequest()->getQuery('input') === 'back') {
                if (!$this->getRequest()->getSession()->check('users.edit.' . $id)) {
                    throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
                }
                $userInputs = $this->getRequest()->getSession()->read('users.edit.' . $id . '.data');
            }
            $userForm->initializeUserEntity($userInputs);
        }

        // セッション削除
        $this->getRequest()->getSession()->delete('users.edit.' . $id);

        // トークン生成
        $this->TokenValidation->generate($tokenKey . '_' . $id);

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Edit conf method
     *
     * @param int|null $id 会員ID
     * @param \App\Form\Common\Users\UserForm $userForm 会員フォーム
     * @param string $tokenKey トークンキー
     * @param mixed $mailSendFlg メール送信フラグ
     * @param array|null $saveOperation 操作ログ
     * @return bool
     */
    protected function editConfAction($id, $userForm, $tokenKey, $mailSendFlg, $saveOperation = null)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        // セッションチェック
        if (!$this->getRequest()->getSession()->check('users.edit.' . $id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // データ取得
        $userForm->setUserEntity($usersTable->get($id, [
            'finder' => 'edit',
        ]));
        if (!$userForm->getUserEntity()->canEdit()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // 会員パラメータチェック
        $userForm->setConfirm(true);
        $userForm->setUserParameter($this->getRequest()->getSession()->read('users.edit.' . $id . '.parameter'));
        if (!$userForm->validateUserParameter()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            // データ更新
            $userInputs = $this->getRequest()->getSession()->read('users.edit.' . $id . '.data');
            if ($userForm->execute((array)$userInputs)) {
                if ($this->TokenValidation->validate($tokenKey . '_' . $id)) {
                    $saveOptions = [
                        'mailSendFlg' => $mailSendFlg,
                        'cryptPassword' => $this->getRequest()->getSession()->read('users.edit.' . $id . '.crypt'),
                        'saveOperation' => $saveOperation,
                    ];
                    if ($usersTable->save($userForm->getUserEntity(), $saveOptions)) {
                        $this->getRequest()->getSession()->delete('users.edit.' . $id);

                        return true;
                    }
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'usersError',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ初期化
            $userForm->initializeUserEntity($this->getRequest()->getSession()->read('users.edit.' . $id . '.data'));
        }

        // トークン生成
        $this->TokenValidation->generate($tokenKey . '_' . $id);

        $this->set([
            'userForm' => $userForm,
            'valueOptions' => $userForm->getFieldValueOptions(),
        ]);

        return false;
    }

    /**
     * Edit finish method
     *
     * @param int|null $id 会員ID
     * @return void
     */
    protected function editFinishAction($id)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');

        if (!$usersTable->validatePrimaryKey($id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->set([
            'userId' => $id,
        ]);
    }
}
