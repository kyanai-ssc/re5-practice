<?php
use App\Controller\AppController;

return [
    'Access' => [
        'ip' => [
            'admin' => [
            ],
            'user' => [
            ],
        ],
        'basic' => [
            'admin' => [
            ],
            'user' => [
            ],
        ],
        'satisfy' => AppController::ACCESS_SATISFY_ALL,
    ],
];
