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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use App\Command\Client;
use App\Command\Setting;
use App\Http\Session;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Console\CommandCollection;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        // Load more plugins here
        $this->addPlugin('Search');
        $this->addPlugin('Authentication');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $csrf = new CsrfProtectionMiddleware([
            'httponly' => true,
            'secure' => Configure::read('Client.secure', true),
        ]);
        $csrf->skipCheckCallback(function ($request) {
            $prefix = $request->getParam('prefix');
            $controller = $request->getParam('controller');
            $action = $request->getParam('action');
            if (!is_scalar($prefix) || !is_scalar($controller) || !is_scalar($action)) {
                return false;
            }

            // API、決済、iframeのカレンダーはCSRFを無効化する
            $allowActions = [
                'User' => [
                    'Payment' => [
                        'finish',
                        'cancel',
                        'error',
                        'result',
                    ],
                ],
            ];
            $allowActionsForFrame = [
                'User/Ajax' => [
                    'Labels' => [
                        'parent',
                    ],
                    'Reservations' => [
                        'calendar',
                        'calendarPage',
                        'calendarPopup',
                    ],
                ],
            ];
            if (
                $prefix === 'Api'
                || (
                    isset($allowActions[$prefix][$controller])
                    && in_array($action, $allowActions[$prefix][$controller], true)
                )
                || (
                    isset($allowActionsForFrame[$prefix][$controller])
                    && in_array($action, $allowActionsForFrame[$prefix][$controller], true)
                    && is_scalar($request->getQuery('frame'))
                )
            ) {
                return true;
            }

            return false;
        });

        $auth = new AuthenticationMiddleware($this);

        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            // Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
            ->add($csrf)

            // Encrypted Cookie
            ->add(new EncryptedCookieMiddleware(
                Configure::readOrFail('Setting.cookie.key'),
                Configure::readOrFail('Security.cookieKey')
            ))

            ->add(function (ServerRequest $request, RequestHandlerInterface $handler) use ($auth) {
                if (
                    $request->getParam('prefix') === 'User' && (
                        $request->getParam('controller') === 'Css'
                        || $request->getParam('controller') === 'File'
                    )
                    || $request->getParam('plugin') === 'DebugKit'
                ) {
                    return $handler->handle($request);
                }

                if (strpos($request->getUri()->getPath(), '/admin') === 0) {
                    $sessionConfig = Configure::readOrFail('SessionAdmin');
                } else {
                    $sessionConfig = Configure::readOrFail('SessionUser');
                    if ($this->shouldSetSameSiteCookie($request)) {
                        $sessionConfig['ini']['session.cookie_samesite']
                            = Configure::readOrFail('Setting.auth.samesite.cookie');
                    }
                }
                $request = $request->withAttribute(
                    'session',
                    Session::create(Hash::merge(Configure::readOrFail('Session'), $sessionConfig))
                );

                return $auth->process($request, $handler);
            });

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    /**
     * @inheritDoc
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands = parent::console($commands);

        $commands->add('client all', Client\AllCommand::class);
        $commands->add('client catch-bounce-mail', Client\CatchBounceMailCommand::class);

        $commands->add('setting new-client', Setting\NewClientCommand::class);
        $commands->add('setting contract-plan', Setting\ContractPlanCommand::class);
        $commands->add('setting default-mail-setting', Setting\DefaultMailSettingCommand::class);
        $commands->add('setting system-admin', Setting\SystemAdminCommand::class);
        $commands->add('setting admin-add', Setting\AdminAddCommand::class);
        $commands->add('setting footer-logo', Setting\FooterLogoCommand::class);
        $commands->add('setting payment-setting', Setting\PaymentSettingCommand::class);
        $commands->add('setting delete-payment-setting', Setting\DeletePaymentSettingCommand::class);
        $commands->add('setting using-payment', Setting\UsingPaymentCommand::class);
        $commands->add('setting export-master-data', Setting\ExportMasterDataCommand::class);
        $commands->add('setting import-master-data', Setting\ImportMasterDataCommand::class);
        $commands->add('setting database-backup', Setting\DatabaseBackupCommand::class);
        $commands->add('setting execute-sql', Setting\ExecuteSqlCommand::class);
        $commands->add('setting analyze-table', Setting\AnalyzeTableCommand::class);
        $commands->add('setting delete-client', Setting\DeleteClientCommand::class);
        $commands->add('setting update-custom-css', Setting\UpdateCustomCssCommand::class);
        $commands->add('setting admin-password-reset', Setting\AdminPasswordResetCommand::class);
        $commands->add('setting admin-password-change', Setting\AdminPasswordChangeCommand::class);
        $commands->add('setting create-app-setting', Setting\CreateAppSettingCommand::class);
        $commands->add('setting smart-lock-setting', Setting\SmartLockSettingCommand::class);
        $commands->add('setting delete-smart-lock-setting', Setting\DeleteSmartLockSettingCommand::class);
        $commands->add('setting buffer-smart-lock-setting', Setting\BufferSmartLockSettingCommand::class);

        return $commands;
    }

    /**
     * 認証情報の設定
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request サーバーリクエストインタフェース
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $fields = [
            'username' => 'login_id',
            'password' => 'password',
        ];
        $loginUrl = Router::url([
            'controller' => 'Auth',
            'action' => 'login',
        ]);
        if (strpos($request->getUri()->getPath(), '/admin') === 0) {
            // 管理側の場合
            $resolver = [
                'className' => 'Authentication.Orm',
                'userModel' => 'Admins',
                'finder' => 'Auth',
            ];
            $sessionKey = [
                'sessionKey' => 'AdminAuth',
            ];
        } else {
            // 公開側の場合
            $resolver = [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
                'finder' => 'Auth',
            ];
            $sessionKey = [
                'sessionKey' => 'UserAuth',
            ];
        }

        $service = new AuthenticationService();
        $service->setConfig([
            'unauthenticatedRedirect' => $loginUrl,
            'queryParam' => 'redirect',
        ]);

        $service->loadIdentifier('Authentication.Password', compact('fields', 'resolver'));
        $service->loadAuthenticator('Authentication.Session', $sessionKey);
        $service->loadAuthenticator('Authentication.Form', compact('fields', 'loginUrl'));

        return $service;
    }

    /**
     * CookieへのSameSite付加判定
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request リクエスト
     * @return bool
     */
    protected function shouldSetSameSiteCookie(ServerRequestInterface $request): bool
    {
        if (
            !Configure::check('Setting.auth.samesite.cookie')
            || (string)Configure::readOrFail('Setting.auth.samesite.cookie') === ''
            || (
                strtolower(Configure::readOrFail('Setting.auth.samesite.cookie')) === 'none'
                && !Configure::read('Client.secure', false)
            )
        ) {
            return false;
        }

        $userAgent = $request->getHeader('USER_AGENT');
        if (!empty($userAgent)) {
            $userAgent = reset($userAgent);
        }

        if (is_string($userAgent)) {
            foreach (Configure::readOrFail('Setting.auth.samesite.exceptUa') as $pattern) {
                if (preg_match($pattern, $userAgent) === 1) {
                    return false;
                }
            }
        }

        return true;
    }
}
