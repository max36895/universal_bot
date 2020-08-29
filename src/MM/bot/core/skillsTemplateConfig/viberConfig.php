<?php
/**
 * @var $userId : Идентификатор пользователя
 * @var $query : Запрос пользователя
 * @var $count : Номер сообщения
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
