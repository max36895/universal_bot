<?php


namespace MM\bot\models\db;


use mysqli;

/**
 * Класс отвечающий за подключение и взаимодействие с Базой Данных
 * Class DB
 * @package bot\models\db
 */
class DB
{
    /**
     * Подключение к базе данных
     * @var mysqli|null $sql
     */
    public $sql;
    /**
     * Ошибки при выполнении запросов
     * @var array $errors
     */
    public $errors;
    /**
     * Параметры для конфигурации имеют следующие поля:
     * [
     *  - string host:  Местоположение базы данных
     *  - string user Имя пользователя
     *  - string pass Пароль пользователя
     *  - string database Название базы данных
     * ]
     * @var array|null $params
     */
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
     * Подключение к базе данных.
     *
     * @return bool
     * @api
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
     * Закрытие подключения к базе данных.
     * @api
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
