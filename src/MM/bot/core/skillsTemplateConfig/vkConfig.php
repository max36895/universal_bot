<?php
/**
 * @var $userId : Идентификатор пользователя
 * @var $query : Запрос пользователя
 * @var $count : Номер сообщения
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
