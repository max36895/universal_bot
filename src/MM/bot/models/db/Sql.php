<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 10.03.2020
 * Time: 9:30
 */

namespace MM\bot\models\db;

use Exception;
use MM\bot\core\mmApp;
use mysqli;
use mysqli_result;

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

if (!isset($vDB)) {
    /**
     * Переменная с коннектом к базе данных. Нужна для того, чтобы не было дополнительных подключений к базе
     */
    $vDB = new DB();
}

/**
 * Class Sql
 * @package bot\models\db
 *
 * @property string $host: Местоположение базы данных
 * @property string $user: Имя пользователя
 * @property string $pass: Пароль пользователя
 * @property string $database: Название базы данных
 */
class Sql
{
    public $host;
    public $user;
    public $pass;
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
     * Настройка подключения к базе данных
     *
     * @return bool
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
     * Инициализация параметров подключения в Базе данных
     *
     * @param string $host : Расположение базы данных
     * @param string $user : Имя пользователя
     * @param string $pass : Пароль
     * @param string $database : Название базы данных
     */
    public function initParam(string $host, $user, $pass, $database): void
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
     * Подключение к Базе данных
     *
     * @return bool
     */
    public function connect(): bool
    {
        global $vDB;
        if ($vDB->connect() === false) {
            $this->saveLog(sprintf("Ошибка при подключении к БД.\n%s" . $vDB->errors[0]));
            return false;
        }
        return true;
    }

    /**
     * Декодирование текста(Текст становится приемлемым для sql запроса)
     *
     * @param string $text : декодируемый текст
     * @return string
     */
    public function escapeString(string $text): string
    {
        global $vDB;
        return $vDB->sql->real_escape_string($text);
    }

    /**
     * Выполнение запроса к базе данных
     *
     * @param string $sql : Текст запроса
     * @return mysqli_result|boolean|null
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
     * Сохранение логов
     *
     * @param string $errorMsg : Текст ошибки
     * @return bool
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
