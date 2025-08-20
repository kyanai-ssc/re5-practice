<?php
return [
    'debug' => false,
    'Error' => [
        'sendMail' => true,
        'sendMailSubject' => '【デモ環境】システムエラーが発生しました。[%client%]',
        'sendMailFrom' => 'system_error@revn5-4.demo.iqnet.co.jp',
        'sendMailTo' => [
            'iq-re@iqnet.co.jp',
        ],
    ],
    'Log' => [
        'debug' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'error' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'command' => [
            'rotate' => 1,
            'size' => 102400,
        ],
        'allClientCommand' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'payment' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'zoom' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'meet' => [
            'rotate' => 1,
            'size' => 1048576,
        ],
        'api' => [
            'rotate' => 1,
            'size' => 1048576,
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
            'php' => '/usr/bin/php',
            'psql' => '/usr/bin/psql',
            'pgDump' => '/usr/bin/pg_dump',
        ],
        'shell' => [
            'background' => 'nohup %s 1>/dev/null 2>/dev/null &',
        ],
        'customerPortal' => '',
        'announce' => [
            'url' => 'https://client-admin.revn5.demo.iqnet.co.jp/api/news/list',
        ],
        'metaRobots' => 'noindex,nofollow,noarchive',
        'systemAdmin' => [
            'loginId' => 're5_demo_ca',
            'ipAddress' => [
            ],
        ],
        'manual' => [
            'domain' => 'https://re5-support.iqnet.co.jp/static/v13',
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
