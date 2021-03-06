<?php
/**
 * Идентификатор пользователя
 * @var string $userId Идентификатор пользователя
 * Запрос пользователя
 * @var string $query Запрос пользователя
 * Номер сообщения
 * @var int $count Номер сообщения
 * Локальные данные пользователя
 * @var array|string|null $state : Локальные данные пользователя
 */

return [
    'meta' => [
        'location' => 'ru-Ru',
        'timezone' => 'UTC',
        'client_id' => 'local',
        'interfaces' => [
            'payments' => null,
            'account_linking' => null
        ]
    ],
    'session' => [
        'message_id' => $count,
        'session_id' => 'local',
        'skill_id' => 'local_test',
        'user_id' => $userId,
        'new' => ($count === 0)
    ],
    'request' => [
        'command' => mb_strtolower($query),
        'original_utterance' => $query,
        'nlu' => [],
        'type' => 'SimpleUtterance'
    ],
    'state' => [
        'session' => $state,
    ],
    'version' => '1.0'
];
