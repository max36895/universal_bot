<?php
/**
 * @var $userId : Идентификатор пользователя
 * @var $query : Запрос пользователя
 * @var $count : Номер сообщения
 */

return [
    'message' => [
        'chat' => [
            'id' => $userId,
        ],
        'message' => [
            'text' => $query,
            'message_id' => $count
        ]
    ]
];
