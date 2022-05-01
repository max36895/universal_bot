<?php

namespace MM\bot\models\db;

use Exception;
use MM\bot\core\mmApp;
use mysqli_result;

if (!isset($vDB)) {
    /**
     * Переменная с подключением к базе данных. Нужна для того, чтобы не было лишних подключений к базе данных.
     * @var DB|null $vDB
     */
    $vDB = new DB();
}

/**
 * Класс, позволяющий работать в Базой Данных
 * Class Sql
 * @package bot\models\db
 */
class Sql
{
    /**
     * Местоположение базы данных.
     * @var string|null $host
     */
    public $host;
    /**
     * Имя пользователя.
     * @var string|null $user
     */
    public $user;
    /**
     * Пароль пользователя.
     * @var string|null $pass
     */
    public $pass;
    /**
     * Название базы данных.
     * @var string|null $database
     */
    public $database;

    /**
     * Sql constructor.
     */
    public function __construct()
    {
        global $vDB;
        if (!$vDB) {
            $vDB = new DB();
        }
        $this->standardInit();
    }

    /**
     * Настройка подключения к базе данных.
     *
     * @return bool
     * @api
     * @throws Exception
     */
    public function standardInit(): bool
    {
        if (isset(mmApp::$config['db'])) {
            $config = mmApp::$config['db'];
            if ($config['host'] && $config['database']) {
                $this->initParam($config['host'], $config['user'], $config['pass'], $config['database']);
            } else {
                $this->saveLog('Sql::standardInit(): Не переданы настройки для подключения к Базе Данных!');
                return false;
            }
            try {
                return $this->connect();
            } catch (Exception $exception) {
                echo $exception;
            }
        }
        return false;
    }

    /**
     * Инициализация параметров подключения в Базе данных.
     *
     * @param string $host Расположение базы данных.
     * @param string $user Имя пользователя.
     * @param string $pass Пароль.
     * @param string $database Название базы данных.
     * @api
     */
    public function initParam(string $host, string $user, string $pass, string $database): void
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
        global $vDB;
        $vDB->params = [
            'host' => $this->host,
            'user' => $this->user,
            'pass' => $this->pass,
            'database' => $this->database
        ];
    }

    /**
     * Подключение к Базе данных.
     *
     * @return bool
     * @api
     * @throws Exception
     */
    public function connect(): bool
    {
        /**
         * @var DB|null $vDB
         */
        global $vDB;
        if ($vDB->connect() === false) {
            $this->saveLog(sprintf("Ошибка при подключении к БД.\n%s" . $vDB->errors[0]));
            return false;
        }
        return true;
    }

    /**
     * Декодирование текста(Текст становится приемлемым для sql запроса).
     *
     * @param string $text декодируемый текст.
     * @return string
     * @api
     */
    public function escapeString(string $text): string
    {
        global $vDB;
        return $vDB->sql->real_escape_string($text);
    }

    /**
     * Выполнение запроса к базе данных.
     *
     * @param string $sql Текст запроса.
     * @return mysqli_result|boolean|null
     * @api
     */
    public function query(string $sql)
    {
        global $vDB;
        if ($vDB->sql) {
            $status = $vDB->sql->query($sql);
            if ($status === false) {
                try {
                    $this->saveLog($vDB->sql->error);
                } catch (Exception $exception) {
                    return null;
                }
            } else {
                return $status;
            }
        }
        return null;
    }

    /**
     * Сохранение логов.
     *
     * @param string $errorMsg Текст ошибки.
     * @return bool
     * @throws Exception
     */
    private function saveLog(string $errorMsg): bool
    {
        if (mmApp::saveLog('sql.log', $errorMsg)) {
            return true;
        }
        echo 'Sql::connect(): Не удалось создать/открыть файл!';
        return false;
        //throw new \Exception('Sql::connect(): Не удалось создать/открыть файл!');
    }
}
