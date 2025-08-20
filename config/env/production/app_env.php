<?php
use Cake\Mailer\Transport\SmtpTransport;

return [
    'debug' => false,
    'Error' => [
        'sendMail' => true,
        'sendMailSubject' => 'システムエラーが発生しました。[%client%]',
        'sendMailFrom' => 'system_error@revn.jp',
        'sendMailTo' => [
            'iq-re@iqnet.co.jp',
        ],
    ],
    'EmailTransport' => [
        'mailDelivery' => [
            'className' => SmtpTransport::class,
            'host' => 'mgw03.db-center.net',
            'port' => 25,
        ],
    ],
    'Log' => [
        'debug' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'error' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'command' => [
            'rotate' => 1,
            'size' => 2097152,
        ],
        'allClientCommand' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'payment' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'zoom' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'meet' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
        'api' => [
            'rotate' => 10,
            'size' => 10485760,
        ],
    ],
    'Env' => [
        'zipSearch' => [
            'options' => [
                'scheme' => 'http',
                'host' => 'api.iqform.jp',
                'url' => '/zip/zipSearch.php',
                'sslVerify' => true,
            ],
        ],
        'path' => [
            'php' => '/usr/bin/php',
            'psql' => '/usr/bin/psql',
            'pgDump' => '/usr/bin/pg_dump',
        ],
        'shell' => [
            'background' => 'nohup %s 1>/dev/null 2>/dev/null &',
        ],
        'customerPortal' => 'https://c-portal.iqnet.co.jp/',
        'announce' => [
            'url' => 'https://client-admin.revn.jp/api/news/list',
        ],
        'metaRobots' => 'index,follow',
        'systemAdmin' => [
            'loginId' => 're5_real_ca',
            'ipAddress' => [
                '124.37.96.190',
                '124.37.96.187',
            ],
        ],
        'manual' => [
            'domain' => 'https://re5-support.iqnet.co.jp',
        ],
        'videoMeeting' => [
            'zoom' => [
                'scheme' => 'https',
                'host' => 'api.zoom.us',
                'hostOAuth' => 'zoom.us',
                'baseUrl' => '/v2',
                'sslVerify' => true,
            ],
            'meet' => [
                'applicationName' => null,
            ],
        ],
        'zoomApi' => [
            'redirectUri' => 'https://zoom.revn.jp/code.html',
            'addUrl' => 'https://zoom.us/oauth/authorize?response_type=code&client_id=eOnSPsBeTNWu7sigB0Dq9g&redirect_uri=https%3A%2F%2Fzoom.revn.jp%2Fcode.html',
        ],
        'recaptcha' => [
            'config' => [
                'scheme' => 'https',
                'host' => 'www.google.com',
                'baseUrl' => '/recaptcha/api',
                'sslVerify' => true,
            ],
        ],
    ],
];
