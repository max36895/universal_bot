<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.1.0
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
            'name' => 'bigImage',
            'slots' => [
                'картинка',
                'изображен'
            ]
        ],
        [
            'name' => 'list',
            'slots' => [
                'список',
                'галер'
            ]
        ],
        [
            'name' => 'save',
            'slots' => [
                'сохрани',
                'save'
            ]
        ]
    ]
];
