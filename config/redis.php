<?php

return [
    'class' => '\common\components\redis\Redis',
    'host' => '127.0.0.1',
    'port' => 6379,
    'enableSlaves' => true,
//    'master' => [
//        [
//            'host' => '127.0.0.1',
//            'port' => 6379
//        ]
//    ],
    'slaves' => [
        [
            'host' => '127.0.0.1',
            'port' => 6879,
        ],
        [
            'host' => '127.0.0.1',
            'port' => 6979
        ]
    ]
];