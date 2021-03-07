<?php

namespace MM\bot\models\db;

/**
 * Вспомогательный класс, хранящий данные для запросов к базе данных.
 * Class QueryData
 * @package MM\bot\models\db
 */
class QueryData
{
    /**
     * Запрос, по которому осуществляется поиск.
     * Представляет из себя массив вида:
     * [
     *      'key' => 'value'
     * ]
     * key - Название поля БД
     * value - Значение, по которому будет осуществляться поиск
     * @var
     */
    protected $query;
    /**
     * Данные, которые будут добавлены/обновлены в БД.
     * Представляет из себя массив вида:
     * [
     *      'key' => 'value'
     * ]
     * key - Название поля БД
     * value - Значение, которое будет добавлено в поле
     * @var
     */
    protected $data;

    public function __construct(array $query = null, array $data = null)
    {
        $this->setQuery($query);
        $this->setData($data);
    }

    /**
     * Получение корректных данных для запроса из строки.
     *
     * @param string $str Строка для парсинга запроса.
     * @return array|null
     */
    public static function getQueryData(string $str): ?array
    {
        $pattern = "/((`[^`]+`)=((\\\"[^\"]+\\\")|([^ ]+)))/umu";
        preg_match_all($pattern, $str, $data);
        if (isset($data[0][0])) {
            $result = [];
            foreach (($data[2] ?? []) as $index => $val) {
                $indexKey = str_replace('`', '', $val);
                $result[$indexKey] = str_replace('"', '', $data[3][$index]);
            }
            return $result;
        }
        return null;
    }

    /**
     * Получение данных для выполнения запроса.
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Установка данных для получения запроса
     * @param mixed $query
     */
    public function setQuery($query)
    {
        if (!is_array($query)) {
            $query = null;
        }
        $this->query = $query;
    }

    /**
     * Получение данных, которые необходимо добавить/обновить.
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Установить данные, которые будет добавлены/обновлены
     * @param mixed $data
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            $data = null;
        }
        $this->data = $data;
    }
}