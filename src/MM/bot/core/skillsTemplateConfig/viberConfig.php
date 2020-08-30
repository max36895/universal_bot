<?php
/**
 * Идентификатор пользователя
 * @var string $userId Идентификатор пользователя
 * Запрос пользователя
 * @var string $query Запрос пользователя
 * Номер сообщения
 * @var int $count Номер сообщения
 */

return [
    'event' => 'message',
    'message' => [
        'text' => $query,
        'type' => 'text'
    ],
    'message_token' => time(),
    'sender' => [
        'id' => $userId,
        'name' => 'local_name',
        'api_version' => 8
    ]
];
