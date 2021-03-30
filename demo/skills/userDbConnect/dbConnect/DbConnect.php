<?php

use MM\bot\api\request\Request;
use \MM\bot\models\db\IModelRes;
use MM\bot\models\db\QueryData;

class DbConnect extends \MM\bot\models\db\DbControllerModel
{
    /**
     * Переменная, отвечающая за отправку curl запросов
     * @var Request
     */
    private $query;

    public function __construct()
    {
        parent::__construct();
        /**
         * Будем отправлять запрос на какой-то сервис
         */
        $this->query = new Request();
        $this->query->url = 'https://query.ru/query';
    }

    /**
     * Приводим полученный результат из запроса к требуемому виду.
     * В данном случае, ожидаем что полученные данные будут вида:
     * [
     *  'key' => 'value'
     * ]
     *
     * @param IModelRes|null $res
     * @return mixed|null
     */
    public function getValue(?IModelRes $res)
    {
        return $res->data;
    }

    /**
     * Отправляем запрос на получение данных
     *
     * @param array $select
     * @param bool $isOne
     * @return IModelRes
     */
    public function select(array $select, bool $isOne = false): IModelRes
    {
        $this->query->post = [
            'type' => 'select',
            'table' => $this->tableName,
            'select' => $select
        ];
        $res = $this->query->send();
        if ($res['status']) {
            if ($isOne) {
                return (new IModelRes(true, $res['data'][0]));
            } else {
                return (new IModelRes(true, $res['data']));
            }
        }
        return (new IModelRes(false, null, $res['err']));
    }

    /**
     * Отправляем запрос на добавление данных
     *
     * @param QueryData $insertData
     * @return mixed|IModelRes
     */
    public function insert(QueryData $insertData)
    {
        $this->query->post = [
            'type' => 'insert',
            'table' => $this->tableName,
            'data' => $insertData
        ];
        $res = $this->query->send();
        if ($res['status']) {
            return (new IModelRes(true, $res['data']));
        }
        return (new IModelRes(false, null, $res['err']));
    }

    /**
     * Выполняем запрос на обновление данных
     *
     * @param QueryData $updateData
     * @return mixed|IModelRes
     */
    public function update(QueryData $updateData)
    {
        $this->query->post = [
            'type' => 'update',
            'table' => $this->tableName,
            'data' => $updateData
        ];
        $res = $this->query->send();
        if ($res['status']) {
            return (new IModelRes(true, $res['data']));
        }
        return (new IModelRes(false, null, $res['err']));
    }

    /**
     * Выполняем запрос на сохранение данных.
     * Тут сть в том, что если данных для обновления нет, то будет добавлена новая запись.
     *
     * @param QueryData $insertData
     * @param bool $isNew
     * @return mixed|IModelRes
     */
    public function save(QueryData $insertData, bool $isNew = false)
    {
        $this->query->post = [
            'type' => 'save',
            'table' => $this->tableName,
            'data' => $insertData
        ];
        $res = $this->query->send();
        if ($res['status']) {
            return (new IModelRes(true, $res['data']));
        }
        return (new IModelRes(false, null, $res['err']));
    }

    /**
     * Выполняем запрос на удаление данных
     *
     * @param QueryData $deleteData
     * @return mixed|IModelRes
     */
    public function delete(QueryData $deleteData)
    {
        $this->query->post = [
            'type' => 'delete',
            'table' => $this->tableName,
            'data' => $deleteData
        ];
        $res = $this->query->send();
        if ($res['status']) {
            return (new IModelRes(true, $res['data']));
        }
        return (new IModelRes(false, null, $res['err']));
    }

    /**
     * Выполняем произвольный запрос
     *
     * @param string $sql
     * @return mixed|IModelRes
     */
    public function query(string $sql)
    {
        $this->query->post = [
            'type' => 'query',
            'table' => $this->tableName,
            'query' => $sql
        ];
        $res = $this->query->send();
        if ($res['status']) {
            return (new IModelRes(true, $res['data']));
        }
        return (new IModelRes(false, null, $res['err']));
    }
}
