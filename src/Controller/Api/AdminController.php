<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\ApiAppController;
use App\Form\Api\Admin\CheckForm;
use App\Form\Api\Admin\LoginForm;
use App\Model\Entity\Admin;
use Exception;

/**
 * Admin Controller
 */
class AdminController extends ApiAppController
{
    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        /** @var \App\Model\Table\AppSettingsTable $appSettingsTable */
        $appSettingsTable = $this->fetchTable('AppSettings');
        /** @var \App\Model\Table\AppAccessTokensTable $appAccessTokensTable */
        $appAccessTokensTable = $this->fetchTable('AppAccessTokens');

        // パラメータチェック
        $loginForm = new LoginForm();
        try {
            if (!$loginForm->execute((array)$this->getRequest()->getData())) {
                $this->setApiResult(
                    static::API_RESULT_STATUS_BAD_REQUEST,
                    static::API_RESULT_MESSAGE_REQUIRED,
                    $this->getRequest()->getData('login_id')
                );

                return;
            }

            /** @var \App\Model\Entity\AppSetting $appSetting */
            $appSetting = $appSettingsTable->getAppSetting();
            if (!$appSetting->checkApiSecret($loginForm->getData('api_secret'))) {
                $this->setApiResult(
                    static::API_RESULT_STATUS_NOT_FOUND,
                    static::API_RESULT_MESSAGE_ACCOUNT_NOT_EXIST,
                    $loginForm->getData('login_id')
                );

                return;
            }

            // 管理者の情報を取得
            $admin = $this->fetchTable('Admins')->find('apiLogin', [
                'inputs' => [
                    'login_id' => $loginForm->getData('login_id'),
                    'password' => $loginForm->getData('password'),
                ],
            ])->first();

            if ($admin instanceof Admin) {
                $this->commonData()->setAdminLoginData($admin);

                // 権限のチェック
                if (!$this->checkAuthority($admin)) {
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_NOT_AUTHORIZED,
                        $loginForm->getData('login_id')
                    );

                    return;
                }

                // トークンの取得
                /** @var \App\Model\Entity\AppAccessToken $appAccessToken */
                $appAccessToken = $appAccessTokensTable->createEntity($admin['id']);
                if ($appAccessTokensTable->save($appAccessToken)) {
                    $result = ['access_token' => $appAccessToken->get('token')];
                    $this->setApiResult(
                        static::API_RESULT_STATUS_OK,
                        static::API_RESULT_MESSAGE_SUCCESS,
                        $loginForm->getData('login_id'),
                        $result
                    );

                    return;
                }
            } else {
                // 管理者が存在しない
                $this->setApiResult(
                    static::API_RESULT_STATUS_NOT_FOUND,
                    static::API_RESULT_MESSAGE_ACCOUNT_NOT_EXIST,
                    $loginForm->getData('login_id')
                );

                return;
            }
        } catch (Exception $e) {
            $this->setExceptionApiResult($e, $this->getRequest()->getData('login_id'));
        }
    }

    /**
     * Check method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function check()
    {
        // パラメータチェック
        $checkForm = new CheckForm();
        try {
            if (!$checkForm->execute((array)$this->getRequest()->getData())) {
                $this->setApiResult(
                    static::API_RESULT_STATUS_BAD_REQUEST,
                    static::API_RESULT_MESSAGE_REQUIRED,
                    $this->getRequest()->getData('access_token')
                );

                return;
            }

            $accessToken = $checkForm->getData('access_token');
            if ($this->checkAccesToken($accessToken, $accessToken)) {
                $this->setApiResult(
                    static::API_RESULT_STATUS_OK,
                    static::API_RESULT_MESSAGE_SUCCESS,
                    $accessToken
                );
            }
        } catch (Exception $e) {
            $this->setExceptionApiResult($e, $this->getRequest()->getData('access_token'));
        }
    }
}
