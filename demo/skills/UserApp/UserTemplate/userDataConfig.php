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
    'userId' => $userId,
    'data' => [
        'text' => strtolower($query),
    ],
    'version' => '1.0'
];
