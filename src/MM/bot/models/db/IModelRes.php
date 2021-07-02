<?php

namespace MM\bot\models\db;

/**
 * Интерфейс для возврата результата выполнения запроса к бд
 * Class IModelRes
 * @package MM\bot\models\db
 */
class IModelRes
{
    /**
     * Статус выполнения запроса
     * @var bool
     */
    public $status;
    /**
     * Ошибки, возникшие во время выполнения запроса
     * @var string|null
     */
    public $error;
    /**
     * Полученный результат запроса
     * @var mixed
     */
    public $data;

    /**
     * IModelRes constructor.
     * @param bool $status Статус выполнения запроса
     * @param null $data Полученный результат запроса
     * @param null|string $error Ошибки, возникшие во время выполнения запроса
     */
    public function __construct(bool $status = false, $data = null, ?string $error = '')
    {
        $this->status = $status;
        $this->data = $data;
        $this->error = $error;
        return $this;
    }
}