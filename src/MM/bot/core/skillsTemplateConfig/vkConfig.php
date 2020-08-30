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
    'type' => 'message_new',
    'object' => [
        'message' => [
            'from_id' => $userId,
            'text' => $query,
            'id' => $count
        ]
    ]
];
