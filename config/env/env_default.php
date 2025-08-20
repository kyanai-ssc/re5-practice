<?php
return [
    'Datasources' => [
        'default' => [
            'host' => 'localhost',
        ],
    ],
    'Security' => [
        'salt' => '__SALT__',
        'cookieKey' => '__COOKIE_KEY__',
    ],
    'Env' => [
        'name' => '__ENV_NAME__',
        'announce' => [
            'apiKey' => '__ANNOUNCE_API_KEY__',
        ],
        'systemAdmin' => [
            //'hashedPassword' => null,
        ],
        'videoMeeting' => [
            'salt' => '__VIDEO_MEETING_SALT__',
        ],
        'zoomApi' => [
            'clientId' => '__ZOOM_CLIENT_ID__',
            'clientSecret' => '__ZOOM_CLIENT_SECRET__',
        ],
        'appSetting' => [
            'salt' => '__APP_SETTING_SALT__',
        ],
        'smartLock' => [
            'remoteLock' => [
                'apiUrl' => 'https://api.remotelock-pf.jp',
                'authUrl' => 'https://api.remotelock-pf.jp',
                'tokenRetryCount' => 5,
                'delaySecond' => 1,
            ],
            'akerun' => [
                'apiUrl' => 'https://api.akerun.com/v3',
                'authUrl' => 'https://api.akerun.com',
                'tokenRetryCount' => 5,
                'delaySecond' => 1,
            ],
        ],
        'mail' => [
            'dkim' => [
                /**
                * @todo 環境によって設定値を変更する
                *
                * - local       : 任意のメールアドレス
                * - development : auto-reply@revn5-ub.test.local
                * - staging     : auto-reply@revn5.demo.iqnet.co.jp
                * - production  : auto-reply@revn.jp
                */
                // 'defaultFrom' => '',
            ],
        ],
        'recaptchaSetting' => [
            'salt' => '__RECAPTCHA_SETTING_SALT__',
        ],
    ],
];
