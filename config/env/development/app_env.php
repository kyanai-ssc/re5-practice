<?php
return [
    'debug' => false,
    'Error' => [
        'sendMail' => false,
        'sendMailSubject' => 'システムエラーが発生しました。[%client%]',
        'sendMailFrom' => '',
        'sendMailTo' => [
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
        'customerPortal' => '#',
        'announce' => [
            'url' => 'https://re5-client-admin.test.local/api/news/list',
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
            'redirectUri' => 'https://basic.revn5-3.demo.iqnet.co.jp/zoom/code.html',
            'addUrl' => 'https://zoom.us/oauth/authorize?response_type=code&client_id=uYVv7HYAQf2lDmroJUKoWg&redirect_uri=https%3A%2F%2Fbasic.revn5-3.demo.iqnet.co.jp%2Fzoom%2Fcode.html',
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
