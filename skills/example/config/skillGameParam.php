<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 10.03.2020
 * Time: 13:31
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
            'name' => 'game',
            'slots' => [
                'игра',
                'начать игру'
            ]
        ],
    ]
];
