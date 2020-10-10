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
    'message' => [
        'chat' => [
            'id' => $userId,
        ],
        'text' => $query,
        'message_id' => $count
    ]
];
