<?php

namespace MM\bot\models\db;


use MM\bot\core\mmApp;

/**
 * Абстрактный класс служащий прослойкой между логикой ядра и подключением к БД.
 * Необходим для корректной настройки контролла, отвечающего за сохранение пользовательских данных.
 * Все прикладные контроллы должны быть унаследованы от него.
 *
 * Class DbControllerModel
 * @package MM\bot\models\db
 */
abstract class DbControllerModel
{
    /**
     * Название таблицы
     * @var
     */
    public $tableName;

    /**
     * Правила для полей бд. Указывается тип каждого поля.
     * @var
     */
    protected $rules;

    /**
     * Конфигурация для настройки подключения к БД.
     * @see mmApp::$config
     * @var
     */
    protected $connectConfig;

    /**
     * Название поля, которое является уникальным ключом
     * @var
     */
    protected $primaryKeyName;

    public function __construct()
    {
        $this->connectConfig = mmApp::$config['db'];
    }

    /**
     * Установить имя уникального ключа
     * @param mixed $primaryKeyName
     */
    public function setPrimaryKeyName($primaryKeyName)
    {
        $this->primaryKeyName = $primaryKeyName;
    }

    /**
     * получение имени уникального ключа
     * @return mixed
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    /**
     * Установить правила для полей
     * @param $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Приводим полученный результат к требуемому типу.
     * В качестве результата должен вернуться массив вида:
     * [
     *    key => value
     * ]
     * где key - порядковый номер поля(0, 1... 3), либо название поля. Рекомендуется использовать имя поля. Важно чтобы имя поля было указано в rules, имена не входящие в rules будут проигнорированы.
     * value - значение поля.
     * @param IModelRes|null $res Результат выполнения запроса
     * @return mixed
     * @see select
     */
    public abstract function getValue(?IModelRes $res);

    /**
     * Выполнение запроса на поиск записей в таблице
     * Возвращает массив вида:
     * [
     *      'status': bool,
     *      'data': mixed,
     *      'error': string
     * ],
     * где:
     * status - статус выполнения запроса
     * data - результат выполнения запроса
     * error - ошибки, возникшие во время выполнения запроса
     *
     * @param array $select Данные для поиска значения
     * @param bool $isOne Вывести только 1 запись.
     * @return IModelRes
     */
    public abstract function select(array $select, bool $isOne = false): IModelRes;

    /**
     * Выполнение запроса на добавление записи в таблицу
     *
     * @param QueryData $insertData Данные для добавления записи
     * @return mixed
     */
    public abstract function insert(QueryData $insertData);

    /**
     * Выполнение запроса на обновление записи в таблице
     *
     * @param QueryData $updateData Данные для обновления записи
     * @return mixed
     */
    public abstract function update(QueryData $updateData);

    /**
     * Выполнение запроса на сохранения записи.
     * Обновление записи происходит в том случае, если запись присутствует в таблице.
     * Иначе будет добавлена новая запись.
     *
     * @param QueryData $insertData Данные для сохранения записи
     * @param bool $isNew В любом случае выполнить добавление записи
     * @return mixed
     */
    public abstract function save(QueryData $insertData, bool $isNew = false);

    /**
     * Выполнение запроса на удаление записи в таблице
     *
     * @param QueryData $deleteData Данные для удаления записи
     * @return mixed
     */
    public abstract function delete(QueryData $deleteData);

    /**
     * Выполнение произвольного запроса к таблице
     *
     * @param string $sql Запрос, который необходимо выполнить
     * @return mixed
     */
    public abstract function query(string $sql);

    /**
     * Декодирование текста(Текст становится приемлемым и безопасным для sql запроса).
     *
     * @param string $str Исходный текст
     * @return string
     */
    public function escapeString(string $str): string
    {
        return $str;
    }
}
