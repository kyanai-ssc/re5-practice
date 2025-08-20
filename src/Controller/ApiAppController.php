<?php
declare(strict_types=1);

namespace App\Controller;

use App\Error\ErrorLoggerTrait;
use App\Locale\Message;
use App\View\JsonView;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;

/**
 * Api Application Controller
 */
class ApiAppController extends AppController
{
    use ErrorLoggerTrait;

    public const AUTHORITY_CHECK_CONTROLLER_NAME = 'ReceptionStatuses';
    public const AUTHORITY_CHECK_ACTION_NAME = 'list';

    public const API_RESULT_STATUS_OK = 200;
    public const API_RESULT_STATUS_BAD_REQUEST = 400;
    public const API_RESULT_STATUS_NOT_FOUND = 404;
    public const API_RESULT_STATUS_INTERNAL_SERVER_ERROR = 500;

    public const API_RESULT_MESSAGE_SUCCESS = 'success';
    public const API_RESULT_MESSAGE_REQUIRED = 'required';
    public const API_RESULT_MESSAGE_ACCESS_TOKEN_NOT_EXIST = 'access token not exist';
    public const API_RESULT_MESSAGE_EXPRIED_TOKEN = 'expried token';
    public const API_RESULT_MESSAGE_NOT_AUTHORIZED = 'not authorized';
    public const API_RESULT_MESSAGE_ACCOUNT_NOT_EXIST = 'account not exist';
    public const API_RESULT_MESSAGE_NOT_ACCEPTED = 'not accepted';
    public const API_RESULT_MESSAGE_ABSENCE = 'absence';
    public const API_RESULT_MESSAGE_DATA_NOT_EXIST = 'data not exist';
    public const API_RESULT_MESSAGE_NOT_RESERVED_DATE = 'not reserved date';

    public const API_ERROR_LOG_MESSAGE = 'コード：%s メッセージ：%s リクエスト：%s';

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->noFormProtectionComponent = true;
        $this->noAuthenticationComponent = true;

        parent::initialize();

        if (!$this->getRequest()->is('post')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
    }

    /**
     * @inheritDoc
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * APIのレスポンスを設定
     *
     * @param string|int $code コード
     * @param string $message メッセージ
     * @param null|string|array $request リクエスト
     * @param array|null $result リザルト
     * @return void
     */
    protected function setApiResult($code, $message, $request, $result = null)
    {
        $this->set([
            'code' => $code,
            'message' => $message,
        ]);
        $this->viewBuilder()->setOption('serialize', [
            'code',
            'message',
        ]);
        if (!empty($result)) {
            $this->set([
                'result' => $result,
            ]);
            $this->viewBuilder()->setOption('serialize', [
                'result',
            ]);
        }
        $this->viewBuilder()->setOption('jsonOptions', JSON_UNESCAPED_UNICODE);

        if (((string)$code) !== ((string)static::API_RESULT_STATUS_OK)) {
            // エラーログを出力
            $this->writeLog($code, $message, $request);
        }
        $this->setResponse($this->getResponse()->withStatus((int)$code));
    }

    /**
     * ログ出力
     *
     * @param string|int $code コード
     * @param string $message メッセージ
     * @param null|string|array $request リクエスト
     * @return void
     */
    protected function writeLog($code, $message, $request)
    {
        // 配列の場合はjsonにエンコードする
        if (is_array($request)) {
            $request = json_encode($request, JSON_UNESCAPED_UNICODE);
            if ($request === false) {
                $request = '';
            }
        }
        Log::error(sprintf(static::API_ERROR_LOG_MESSAGE, $code, $message, $request), ['scope' => 'api']);
    }

    /**
     * アクセストークンのチェックを行う
     *
     * @param string $accessToken アクセストークン
     * @param null|string|array $request ログに出力するデータ
     * @return bool
     */
    protected function checkAccesToken($accessToken, $request)
    {
        /** @var \App\Model\Table\AppAccessTokensTable $appAccessTokensTable */
        $appAccessTokensTable = $this->fetchTable('AppAccessTokens');

        $appAccessToken = $appAccessTokensTable->getAppAccessToken($accessToken);
        //アクセストークンが存在するか検証
        if (!empty($appAccessToken)) {
            //管理者権限が存在するか検証
            $admin = $appAccessToken->get('admin');
            if (isset($admin)) {
                // 管理者の情報を取得
                $this->commonData()->setAdminLoginData($admin);
                // 権限のチェック
                if (!$this->checkAuthority($admin)) {
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_NOT_AUTHORIZED,
                        $request
                    );

                    return false;
                }

                // トークンの取得
                if ($appAccessToken->get('expiration_timestamp') <= $this->commonData()->getNowDateTime()) {
                    //トークンの有効期限が切れている
                    $this->setApiResult(
                        static::API_RESULT_STATUS_BAD_REQUEST,
                        static::API_RESULT_MESSAGE_EXPRIED_TOKEN,
                        $request
                    );

                    return false;
                }
            } else {
                // 管理者が存在しない
                $this->setApiResult(
                    static::API_RESULT_STATUS_NOT_FOUND,
                    static::API_RESULT_MESSAGE_ACCOUNT_NOT_EXIST,
                    $request
                );

                return false;
            }
        } else {
            // トークンが存在しない
            $this->setApiResult(
                static::API_RESULT_STATUS_BAD_REQUEST,
                static::API_RESULT_MESSAGE_ACCESS_TOKEN_NOT_EXIST,
                $request
            );

            return false;
        }

        return true;
    }

    /**
     * 管理者が受付状況一覧にアクセスできるかチェック
     *
     * @param \Cake\Datasource\EntityInterface $admin Admin Entity
     * @return bool
     */
    protected function checkAuthority($admin)
    {
        /** @var \App\Model\Table\AdminAuthoritiesTable $adminAuthoritiesTable */
        $adminAuthoritiesTable = $this->fetchTable('AdminAuthorities');

        // 機能の利用設定の設定値をセット
        $data = $adminAuthoritiesTable->getAccessData($admin->get('admin_authority'));
        $this->Authority->setAuthority($data);

        return $this->Authority->checkAuthority(
            static::AUTHORITY_CHECK_CONTROLLER_NAME,
            static::AUTHORITY_CHECK_ACTION_NAME
        );
    }

    /**
     * 例外発生時のレスポンス
     *
     * @param \Exception $e Exception
     * @param null|string|array $request リクエスト
     * @return void
     */
    protected function setExceptionApiResult($e, $request)
    {
        // ログを出力
        $this->getErrorLogger()->log($e, $this->getRequest());
        // エラーメールの送信
        $this->getErrorLogger()->sendExceptionMail($e);

        $this->setApiResult(
            static::API_RESULT_STATUS_INTERNAL_SERVER_ERROR,
            '',
            $request
        );
    }
}
