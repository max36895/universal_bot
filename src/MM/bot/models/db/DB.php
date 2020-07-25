<?php


namespace MM\bot\models\db;


use mysqli;

/**
 * Class DB
 * @package bot\models\db
 *
 * @property mysqli $sql: Подключение к базе данных
 * @property array $errors: Ошибки при выполнении запросов
 * @property array $params: параметры для конфигурации. имеют следующие поля:
 *  - @var string host: Местоположение базы данных
 *  - @var string user: Имя пользователя
 *  - @var string pass: Пароль пользователя
 *  - @var string database: Название базы данных
 */
class DB
{
    public $sql;
    public $errors;
    public $params;

    /**
     * DB constructor.
     */
    public function __construct()
    {
        $this->sql = null;
        $this->errors = [];
        $this->params = null;
    }

    /**
     * Подключение к базе данных
     *
     * @return bool
     */
    public function connect(): bool
    {
        $this->errors = [];
        if ($this->params) {
            $this->close();
            $this->sql = new mysqli($this->params['host'], $this->params['user'], $this->params['pass'], $this->params['database']);
            if (!$this->sql->connect_errno) {
                $this->sql->query('SET NAMES utf8mb4');
                $this->sql->query('SET CHARACTER SET utf8mb4');
                $this->sql->query('SET COLLATION_CONNECTION="utf8mb4_general_ci"');
                return true;
            }
            $this->errors[] = $this->sql->connect_errno;
        } else {
            $this->errors[] = 'Отсутствуют данные для подключения в БД!';
        }
        return false;
    }

    /**
     * Закрытие подключения к базе данных
     */
    public function close()
    {
        if ($this->sql) {
            $this->sql->close();
            $this->sql = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}