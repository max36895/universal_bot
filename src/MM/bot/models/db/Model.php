<?php

namespace MM\bot\models\db;


use MM\bot\core\mmApp;
use mysqli_result;

/**
 * Class Model
 * @package bot\models\db
 *
 * Абстрактный класс для моделей. Все Модели, взаимодействующие с бд наследуют его.
 */
abstract class Model
{
    /**
     * @var DbControllerModel
     */
    public $dbController;

    /**
     * @var QueryData
     */
    public $queryData;

    /**
     * Стартовое значение для индекса.
     * @var int $startIndex
     */
    public $startIndex = 0;

    /**
     * Правила для обработки полей. Где 1 - Элемент это название поля, 2 - Элемент тип поля, max - Максимальная длина.
     *
     * @return array
     * [
     *  - string|array 0: Название поля.
     *  - string 1: Тип поля (text, string, integer, ...).
     *  - int max: Максимальная длина строки.
     * ]
     */
    public abstract function rules(): array;

    /**
     * Массив с полями таблицы, где ключ это название поля, а значение краткое описание.
     * Для уникального ключа использовать значение ID.
     *
     * @return array
     */
    public abstract function attributeLabels(): array;

    /**
     * Название таблицы/файла с данными.
     *
     * @return string
     */
    public abstract function tableName(): string;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        if (mmApp::$userDbController) {
            $this->dbController = mmApp::$userDbController;
        } else {
            $this->dbController = new DbController();
        }
        $this->dbController->tableName = $this->tableName();
        $this->dbController->setRules($this->rules());
        $this->dbController->setPrimaryKeyName($this->getId());
        $this->queryData = new QueryData();
    }

    /**
     * Возвращаем название уникального ключа таблицы.
     *
     * @return int|string|null
     */
    protected function getId()
    {
        foreach ($this->attributeLabels() as $index => $label) {
            if ($label === 'ID' || $label === 'id') {
                return $index;
            }
        }
        return null;
    }

    /**
     * Инициализация данных для модели.
     *
     * @param array $data Массив с данными.
     * @api
     */
    public function init(array $data): void
    {
        $i = $this->startIndex;
        foreach ($this->attributeLabels() as $index => $label) {
            if ($data) {
                if (isset($data[$index])) {
                    $this->$index = $data[$index];
                } elseif (isset($data[$i])) {
                    $this->$index = $data[$i];
                } else {
                    $this->$index = '';
                }
            } else {
                $this->$index = '';
            }
            $i++;
        }
    }

    /**
     * Выполнение запроса с поиском по уникальному ключу.
     *
     * @return bool|mysqli_result|array|null
     * @api
     */
    public function selectOne()
    {
        $idName = $this->dbController->getPrimaryKeyName();
        $this->queryData->setQuery([$idName => $this->$idName]);
        $this->queryData->setData(null);
        return $this->dbController->select($this->queryData->getQuery(), true);
    }

    /**
     * Сохранение значения в базу данных.
     * Если значение уже есть в базе данных, то данные обновятся. Иначе добавляется новое значение.
     *
     * @param bool $isNew Добавить новую запись в базу данных без поиска по ключу.
     * @return bool|mysqli_result|null
     * @api
     */
    public function save(bool $isNew = false)
    {
        $this->validate();
        $idName = $this->dbController->getPrimaryKeyName();
        $this->queryData->setQuery([$idName => $this->$idName]);
        $data = [];
        foreach ($this->attributeLabels() as $index => $label) {
            if ($index !== $idName) {
                $data[$index] = $this->$index;
            }
        }
        $this->queryData->setData($data);
        return $this->dbController->save($this->queryData, $isNew);
    }

    /**
     * Обновление значения в таблице.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function update()
    {
        $this->validate();
        $idName = $this->dbController->getPrimaryKeyName();
        $this->queryData->setQuery([$idName => $this->$idName]);
        $data = [];
        foreach ($this->attributeLabels() as $index => $label) {
            if ($index !== $idName) {
                $data[$index] = $this->$index;
            }
        }
        $this->queryData->setData($data);
        return $this->dbController->update($this->queryData);
    }

    /**
     * Добавление значения в таблицу.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function add()
    {
        $this->validate();
        $this->queryData->setQuery(null);
        $data = [];
        foreach ($this->attributeLabels() as $index => $label) {
            $data[$index] = $this->$index;
        }
        $this->queryData->setData($data);
        return $this->dbController->insert($this->queryData);
    }

    /**
     * Удаление значения из таблицы.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function delete()
    {
        $idName = $this->dbController->getPrimaryKeyName();
        $this->queryData->setQuery([$idName => $this->$idName]);
        $this->queryData->setData(null);
        return $this->dbController->delete($this->queryData);
    }

    /**
     * Выполнение запроса к данным.
     *
     * @param array|string|null $where Запрос к таблице.
     * @param bool $isOne Вывести только 1 результат. Используется только при поиске по файлу.
     * @return bool|mysqli_result|array|null
     * @api
     */
    public function where($where = null, bool $isOne = false)
    {
        if (is_string($where)) {
            $where = QueryData::getQueryData($where);
        }
        return $this->dbController->select($where, $isOne);
    }

    /**
     * Выполнение запроса и инициализация переменных в случае успешного запроса.
     *
     * @param array|string|null $where Запрос к таблице.
     * @return bool
     * @api
     */
    public function whereOne($where = []): bool
    {
        $res = $this->where($where, true);
        $val = $this->dbController->getValue($res);
        if ($val) {
            $this->init($val);
            return true;
        }
        return false;
    }

    public function escapeString(string $str)
    {
        return $this->dbController->escapeString($str);
    }

    public function query(string $sql)
    {
        return $this->dbController->query($sql);
    }

    public function validate()
    {
    }
}
