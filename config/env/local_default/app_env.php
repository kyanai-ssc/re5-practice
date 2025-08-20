<?php
return [
    'debug' => true,
    'Error' => [
        'sendMail' => false,
        'sendMailSubject' => 'システムエラーが発生しました。[%client%]',
        'sendMailFrom' => '',
        'sendMailTo' => [
        ],
    ],
    'EmailTransport' => [
        'default' => [
            'eol' => "\r\n",
        ],
        'mailDelivery' => [
            'eol' => "\r\n",
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
                'scheme' => 'https',
                'host' => 'api.iqform.jp',
                'url' => '/zip/zipSearch.php',
                'sslVerify' => true,
            ],
        ],
        'path' => [
            'php' => 'php',
            'psql' => 'psql',
            'pgDump' => 'pg_dump',
        ],
        'shell' => [
            'background' => '%s 1>nul 2>nul',
        ],
        'customerPortal' => '#',
        'announce' => [
            'url' => '#',
        ],
        'metaRobots' => 'noindex,nofollow,noarchive',
        'systemAdmin' => [
            'loginId' => 'iqadmin',
            'ipAddress' => [
            ],
        ],
        'manual' => [
            'domain' => 'https://re5-support.iqnet.co.jp/static/v13',
        ],
        'videoMeeting' => [
            'zoom' => [
                'scheme' => 'https',
                'host' => '',
                'hostOAuth' => '',
                'baseUrl' => '/v2',
                'sslVerify' => true,
            ],
            'meet' => [
                'applicationName' => null,
            ],
        ],
        'zoomApi' => [
            'redirectUri' => '',
            'addUrl' => '',
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
