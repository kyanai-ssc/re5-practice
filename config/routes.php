<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use App\Model\Entity\FormGroup;
use Cake\Core\Configure;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use  `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    // 公開側
    $routes->scope('/', ['prefix' => 'User'], function (RouteBuilder $builder): void {
        $builder->connect('/', [
            'controller' => 'Index',
            'action' => 'index',
        ]);
        $builder->connect('/{controller}', [
            'action' => 'index',
        ]);

        // ファイル読み取り用
        $builder
            ->connect('/file/{dirName}/{fileName}', [
                'controller' => 'File',
                'action' => 'index',
            ])
            ->setPass(['dirName', 'fileName'])
            ->setPatterns(['dirName' => '[A-Za-z\d_\-]+', 'fileName' => '[A-Za-z\d_\-\.]+']);

        // Css読み取り用
        $builder
            ->connect('/css/{fileName}', [
                'controller' => 'Css',
                'action' => 'index',
            ])
            ->setPass(['fileName'])
            ->setPatterns(['fileName' => '(custom\.css)']);

        // 会員情報
        $builder
            ->connect('/user/password-edit/{id}', [
                'controller' => 'user',
                'action' => 'passwordEdit',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/user/password-edit-finish/{id}', [
                'controller' => 'user',
                'action' => 'passwordEditFinish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/user/withdraw/{id}', [
                'controller' => 'user',
                'action' => 'withdraw',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/user/withdraw-finish/{id}', [
                'controller' => 'user',
                'action' => 'withdrawFinish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/user/mail-edit/{id}', [
                'controller' => 'user',
                'action' => 'mailEdit',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/user/mail-edit-finish/{id}', [
                'controller' => 'user',
                'action' => 'mailEditFinish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // キャンセル待ち通知
        $builder
            ->connect('/waiting-cancellation/release/{id}', [
                'controller' => 'WaitingCancellation',
                'action' => 'release',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // 非会員予約確認
        $builder
            ->connect('/guest/code/{id}', [
                'controller' => 'Guest',
                'action' => 'code',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-detail/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationDetail',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-cancel/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationCancel',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-cancel-finish/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationCancelFinish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-edit/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationEdit',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-edit-conf/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationEditConf',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/guest/reservation-edit-finish/{id}', [
                'controller' => 'Guest',
                'action' => 'reservationEditFinish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // QRコード生成
        $builder
            ->connect('/qrcode/view/{qrCode}', [
                'controller' => 'Qrcode',
                'action' => 'view',
            ])
            ->setPass(['qrCode']);

        // 決済
        foreach (['link', 'finish', 'cancel', 'error', 'error-sb3ds', 'result'] as $action) {
            $builder
                ->connect('/payment/' . $action . '/{id}', [
                    'controller' => 'Payment',
                    'action' => $action,
                ])
                ->setPass(['id'])
                ->setPatterns(['id' => '\\d{1,18}']);
        }

        // IDを指定するアクション
        $builder
            ->connect('/{controller}/view/{id}', [
                'action' => 'view',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/detail/{id}', [
                'action' => 'detail',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/add-finish/{id}', [
                'action' => 'add-finish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit/{id}', [
                'action' => 'edit',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit-conf/{id}', [
                'action' => 'edit-conf',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit-finish/{id}', [
                'action' => 'edit-finish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/cancel/{id}', [
                'action' => 'cancel',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/cancel-finish/{id}', [
                'action' => 'cancel-finish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        $builder->connect('/{controller}/{action}');
    });

    // 公開側ajax
    $routes->scope('/ajax', ['prefix' => 'User/Ajax'], function (RouteBuilder $builder): void {
        $builder->connect('/{controller}/{action}', [
            '_ext' => 'json',
        ]);
        $builder->connect('/{controller}/{action}');
    });

    // 管理側
    $routes->scope('/admin', ['prefix' => 'Admin'], function (RouteBuilder $builder): void {
        $builder->connect('/', [
            'controller' => 'Index',
            'action' => 'index',
        ]);
        $builder->connect('/{controller}', [
            'action' => 'index',
        ]);

        // 基本設定(IDを指定された場合)
        $builder
            ->redirect('/system/edit/{id}', [
                'controller' => 'SiteSetting',
                'action' => 'edit',
            ]);

        // フォームパターン
        $builder->connect(
            '/form-patterns/user/{action}',
            ['controller' => 'FormPatterns', 'formType' => FormGroup::FORM_TYPE_USER],
            ['_name' => 'formPatternsUser']
        );
        $builder->connect(
            '/form-patterns/user/{action}/{id}',
            ['controller' => 'FormPatterns', 'formType' => FormGroup::FORM_TYPE_USER],
            ['_name' => 'formPatternsUserEdit']
        )->setPass(['id'])
        ->setPatterns(['id' => '\\d{1,18}']);
        $builder->connect(
            '/form-patterns/reserve/{action}/',
            ['controller' => 'FormPatterns', 'formType' => FormGroup::FORM_TYPE_RESERVATION],
            ['_name' => 'formPatternsReserve']
        );
        $builder->connect(
            '/form-patterns/reserve/{action}/{id}',
            ['controller' => 'FormPatterns', 'formType' => FormGroup::FORM_TYPE_RESERVATION],
            ['_name' => 'formPatternsReserveEdit']
        )->setPass(['id'])
        ->setPatterns(['id' => '\\d{1,18}']);

        // メール配信履歴
        $builder
            ->connect('/mail-deliveries/send-user-download/{id}', [
                'controller' => 'MailDeliveries',
                'action' => 'sendUserDownload',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // 自動返信メール
        $builder
            ->connect('/auto-reply-mails/replace-vars/{type}', [
                'controller' => 'AutoReplyMails',
                'action' => 'replaceVars',
            ])
            ->setPass(['type'])
            ->setPatterns(['type' => implode('|', array_keys(Configure::readOrFail('Master.autoReplyMail.type')))]);

        // 会員
        $builder
            ->connect('/users/withdraw/{id}', [
                'controller' => 'Users',
                'action' => 'withdraw',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // 決済エラー回数一覧
        $builder
            ->connect('/payment-errors/unlock/{id}', [
                'controller' => 'PaymentErrors',
                'action' => 'unlock',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        //属性承認
        $builder
            ->connect('/users/approval/{id}', [
                'controller' => 'Users',
                'action' => 'approval',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        // IDを指定するアクション
        $builder
            ->connect('/{controller}/view/{id}', [
                'action' => 'view',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/add-finish/{id}', [
                'action' => 'add-finish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/copy/{id}', [
                'action' => 'copy',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit/{id}', [
                'action' => 'edit',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit-conf/{id}', [
                'action' => 'edit-conf',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/edit-finish/{id}', [
                'action' => 'edit-finish',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/cancel/{id}', [
                'action' => 'cancel',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/delete/{id}', [
                'action' => 'delete',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/preview/{id}', [
                'action' => 'preview',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);
        $builder
            ->connect('/{controller}/download/{id}', [
                'action' => 'download',
            ])
            ->setPass(['id'])
            ->setPatterns(['id' => '\\d{1,18}']);

        $builder->connect('/{controller}/{action}');
    });

    // 管理側ajax
    $routes->scope('/admin/ajax', ['prefix' => 'Admin/Ajax'], function (RouteBuilder $builder): void {
        $builder->connect('/{controller}/{action}', [
            '_ext' => 'json',
        ]);
        $builder->connect('/{controller}/{action}');
    });

    // API
    $routes->scope('/api', ['prefix' => 'Api'], function (RouteBuilder $builder): void {
        $builder
            ->connect('/reservations/edit/{qrCodeData}', [
                'controller' => 'Reservations',
                'action' => 'edit',
                '_ext' => 'json',
                '_method' => 'POST',
            ])
            ->setPass(['qrCodeData']);

        $builder->connect('/{controller}/{action}', [
            '_ext' => 'json',
            '_method' => 'POST',
        ]);
        $builder->connect('/{controller}/{action}');
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
