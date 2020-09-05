<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
return [
    'telegram_token' => '',
    'intents' => [
        [
            'name' => 'by',
            'slots' => [
                'пока',
            ]
        ],
        [
            'name' => 'replay',
            'slots' => [
                'повтор',
                'еще раз'
            ]
        ],
        [
            'name' => 'auth',
            'slots' => [
                'регистр',
                'авториз'
            ]
        ],
    ]
];
