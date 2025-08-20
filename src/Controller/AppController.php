<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Auth\BasicAuthenticate;
use App\Http\ServerRequest;
use App\Locale\Message;
use App\Utility\CommonData\CommonDataTrait;
use App\Validation\CustomValidation;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\I18n\FrozenTime;
use Cake\I18n\I18n;
use Cake\Routing\Router;
use Cake\Utility\Hash;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @property \App\Controller\Component\AuthorityComponent $Authority
 * @property \App\Controller\Component\FileDownloadComponent $FileDownload
 * @property \App\Controller\Component\FrameComponent $Frame
 * @property \App\Controller\Component\PaginationComponent $Pagination
 * @property \App\Controller\Component\RequestFilterComponent $RequestFilter
 * @property \App\Controller\Component\SearchInputComponent $SearchInput
 * @property \App\Controller\Component\TokenValidationComponent $TokenValidation
 * @property \App\Controller\Component\RecaptchaComponent $Recaptcha
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Cake\Controller\Component\SecurityComponent $Security
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    use CommonDataTrait;

    public const ENV_X_FRAME_OPTIONS_UNSET = 'X_FRAME_OPTIONS_UNSET';

    public const ACCESS_SATISFY_ALL = 'all';
    public const ACCESS_SATISFY_ANY = 'any';

    /**
     * @var bool $noFormProtectionComponent
     */
    protected $noFormProtectionComponent = false;
    /**
     * @var bool $noAuthenticationComponent
     */
    protected $noAuthenticationComponent = false;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        // APIの場合はloadComponentしない
        if (!$this->noFormProtectionComponent) {
            $this->loadComponent('FormProtection', [
                'validationFailureCallback' => function () {
                    throw new BadRequestException(__(Message::ERROR_ILLEGAL_TRANSITION));
                },
            ]);
        }

        $this->loadComponent('Authority');
        $this->loadComponent('FileDownload');
        $this->loadComponent('Frame');
        $this->loadComponent('Pagination');
        $this->loadComponent('RequestFilter');
        $this->loadComponent('SearchInput', [
            'searchQuery' => Configure::readOrFail('Setting.searchInput.searchQuery'),
            'saveExec' => Configure::readOrFail('Setting.searchInput.saveExec'),
        ]);
        $this->loadComponent('TokenValidation');
        if (!$this->noAuthenticationComponent) {
            $this->loadComponent('Authentication.Authentication');
        }
        $this->loadComponent('Recaptcha');

        $this->viewBuilder()->addHelper('Authority', ['callback' => [$this->Authority, 'checkAuthority']]);

        if (!is_null(env(static::ENV_X_FRAME_OPTIONS_UNSET))) {
            $this->Frame->sameorigin();
        }
    }

    /**
     * Update the query string data.
     *
     * @param array $data The query string data to use
     * @return void
     */
    protected function setRequestQuery($data)
    {
        $this->setRequest($this->getRequest()->withQueryParams($data));
    }

    /**
     * Update the parsed body.
     *
     * @param null|array|object $data The deserialized body data.
     * @return void
     */
    protected function setRequestData($data)
    {
        $this->setRequest($this->getRequest()->withParsedBody($data));
    }

    /**
     * Create a new response with a cookie set.
     *
     * @param string $name The name of the cookie to set.
     * @param array $config An array of cookie options.
     * @param array|string $value Either a string value, or an array of cookie data.
     * @return void
     */
    protected function setResponseCookie($name, $config, $value)
    {
        $data = $config + [
                'secure' => Configure::readOrFail('Client.secure'),
            ];
        if (isset($data['expires']) && $data['expires'] !== '') {
            $expires = new FrozenTime($data['expires']);
            $data['expire'] = $expires->toUnixString();
            unset($data['expires']);
        }
        $data['value'] = $value;

        $this->setResponse($this->getResponse()->withCookie(Cookie::create($name, $data)));
    }

    /**
     * 翻訳ファイルをセットする
     *
     * @param bool $adminFlg 管理側フラグ
     * @return void
     */
    protected function setTranslate($adminFlg = false)
    {
        try {
            /** @var \App\Model\Table\WordsTable $wordsTable */
            $wordsTable = $this->fetchTable('Words');
            $words = $wordsTable->getData($adminFlg);
        } catch (\Exception $e) {
            $words = Configure::read('ErrorMessage');
        }

        /** @var \Cake\I18n\Translator $transrator */
        $transrator = I18n::getTranslator();
        $transrator->getPackage()->setMessages($words);
    }

    /**
     * パラメータのリダイレクトURLをセット
     *
     * @return string|null リダイレクトURL
     */
    protected function redirectToParam()
    {
        $redirect = $this->getRequest()->getQuery('redirect');
        $url = null;
        if (CustomValidation::redirectUrl($redirect)) {
            $url = Router::url($redirect);
        }

        return $url;
    }

    /**
     * ログイン後の遷移先を取得
     *
     * @return string
     */
    protected function getAuthRedirectUrl()
    {
        $redirectUrl = $this->Authentication->getLoginRedirect();
        if (is_null($redirectUrl) || !CustomValidation::redirectUrl($redirectUrl)) {
            $query = (array)$this->getRequest()->getQuery();
            unset($query['redirect']);
            $this->setRequestQuery($query);

            $redirectUrl = $this->Authentication->getLoginRedirect();
            if (is_null($redirectUrl) || !CustomValidation::redirectUrl($redirectUrl)) {
                $redirectUrl = $this->getAuthRedirectUrlDefault();
            }
        }

        return $redirectUrl;
    }

    /**
     * デフォルトのログイン後の遷移先を取得
     *
     * @return string
     */
    protected function getAuthRedirectUrlDefault()
    {
        return '/';
    }

    /**
     * @param string $type admin or user
     * @return void
     */
    protected function canAccess($type)
    {
        $access = Configure::read('Access');
        $ip = Hash::get($access, 'ip.' . $type, []);
        $basic = Hash::get($access, 'basic.' . $type, []);

        $satisfy = false;
        if (Hash::get($access, 'satisfy') === static::ACCESS_SATISFY_ANY) {
            $satisfy = true;
        }

        $serverRequest = new ServerRequest([
            'session' => $this->getRequest()->getSession(),
        ]);
        $ipSuccess = false;
        if (count($ip) >= 1) {
            $ipSuccess = $serverRequest->canAccessIp($ip);
            if (!$ipSuccess && !$satisfy) {
                $this->setResponse($this->getResponse()->withStatus(403));
                throw new ForbiddenException('403 Forbidden', 403);
            }
        }

        //satisfy anyの場合はIPのみチェック
        if ($satisfy && $ipSuccess === true) {
            return;
        }

        if (count($basic) >= 1) {
            $authenticate = new BasicAuthenticate($this->components());
            $basicSuccess = $authenticate->unauthenticatedBasic($this->getRequest(), $basic);
            if (!$basicSuccess) {
                $this->autoRender = false;
                $header = $authenticate->loginHeaders($this->getRequest());

                $response = $this->getResponse()->withStatus(401);
                $response = $response->withAddedHeader('WWW-Authenticate', $header['WWW-Authenticate']);

                $exception = new UnauthorizedException('401 Forbidden', 401);
                $exception->setHeaders($response->getHeaders());
                throw $exception;
            }
        }
    }
}
