<?php

namespace MM\bot\models\db;

use Exception;
use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;
use mysqli_result;


class DbController extends DbControllerModel
{
    /**
     * Подключение к базе данных.
     * @var Sql|null $db
     */
    public $db;


    public function __construct()
    {
        parent::__construct();
        if (mmApp::$isSaveDb) {
            $this->db = new Sql();
        } else {
            $this->db = null;
        }
    }

    /**
     * Приводим полученный результат к требуемому типу.
     * В качестве результата должен вернуться массив вида:
     * [
     *    key => value
     * ]
     * где key - порядковый номер поля(0, 1... 3), либо название поля. Рекомендуется использовать имя поля. Важно чтобы имя поля было указано в rules, имена не входящие в rules будут проигнорированы.
     * value - значение поля.
     *
     * @param array|null $res Результат выполнения запроса
     * @return mixed|null
     * @see select
     */
    public function getValue(?array $res)
    {
        if ($res && $res['status']) {
            if (mmApp::$isSaveDb) {
                $data = $res['data'];
                if ($data && $data->num_rows) {
                    $result = $data->fetch_array(MYSQLI_NUM);
                    $data->free_result();
                    return $result;
                }
                return null;
            } else {
                return $res['data'];
            }
        }
        return null;
    }

    /**
     * Название таблицы/файла с данными.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return "`{$this->tableName}`";
    }

    /**
     * Выполнение запроса на добавление записи в таблицу
     *
     * @param QueryData $insertQuery Данные для добавления записи
     * @return bool|mysqli_result|null
     * @throws Exception
     */
    public function insert(QueryData $insertQuery)
    {
        if ($insertQuery) {
            $insertData = $insertQuery->getData();
            if (mmApp::$isSaveDb) {
                if ($insertData) {
                    $insert = $this->validate($insertData);
                    $into = '';
                    $value = '';
                    foreach ($insert as $index => $val) {
                        if ($into) {
                            $into .= ',';
                        }
                        if ($value) {
                            $value .= ',';
                        }
                        $into .= "`{$index}`";
                        $value .= $val;
                    }
                    $sql = "INSERT INTO {$this->getTableName()} ({$into}) VALUE ({$value});";
                    return $this->db->query($sql);
                }
            } else {
                $data = $this->getFileData();
                $tmp = [];
                foreach ($insertData as $index => $val) {
                    $tmp[$index] = $val;
                }
                $idVal = $insertData[$this->primaryKeyName] ?? null;
                if ($idVal) {
                    $data[$idVal] = $tmp;
                    mmApp::saveJson("{$this->tableName}.json", $data);
                    return true;
                }
            }
        }
        return null;
    }

    /**
     * Выполнение запроса на обновление записи в таблице
     *
     * @param QueryData $updateQuery Данные для обновления записи
     * @return bool|mysqli_result|null
     * @throws Exception
     */
    public function update(QueryData $updateQuery)
    {
        if ($updateQuery) {
            $update = $updateQuery->getData();
            $select = $updateQuery->getQuery();
            if (mmApp::$isSaveDb) {
                $update = $this->validate($update);
                $set = '';
                foreach ($update as $index => $label) {
                    if ($set) {
                        $set .= ',';
                    }
                    $set .= "`{$index}`={$this->$index}";
                }
                $where = '';
                foreach ($select as $index => $val) {
                    if ($where) {
                        $where .= ' AND ';
                    }
                    $where .= "`{$index}`={$val}";
                }
                if ($where) {
                    $sql = "UPDATE {$this->getTableName()} SET {$set} WHERE {$where};";
                    return $this->db->query($sql);
                }
            } else {
                $data = $this->getFileData();
                $idName = current($select);
                if (isset($data[$idName])) {
                    $tmp = [];
                    foreach ($update as $index => $val) {
                        $tmp[$index] = $val;
                    }
                    $data[$idName] = $tmp;
                    mmApp::saveJson("{$this->tableName}.json", $data);
                }
                return true;
            }
        }
        return null;
    }

    /**
     * Выполнение запроса на сохранения записи.
     * Обновление записи происходит в том случае, если запись присутствует в таблице.
     * Иначе будет добавлена новая запись.
     *
     * @param QueryData $queryData Данные для сохранения записи
     * @param bool $isNew В любом случае выполнить добавление записи
     * @return bool|mysqli_result|null
     */
    public function save(QueryData $queryData, bool $isNew = false)
    {
        if ($queryData) {
            if ($isNew) {
                $queryData->setData(mmApp::arrayMerge($queryData->getData(), $queryData->getQuery()));
                return $this->insert($queryData);
            }
            if ($this->isSelected($queryData->getQuery())) {
                return $this->update($queryData);
            } else {
                $queryData->setData(mmApp::arrayMerge($queryData->getData(), $queryData->getQuery()));
                return $this->insert($queryData);
            }
        }
        return false;
    }

    /**
     * Наличие записи в таблице
     *
     * @param array $select Запрос
     * @return bool
     */
    protected function isSelected(array $select): bool
    {
        $res = $this->select($select, true);
        if ($res && $res['status']) {
            if (mmApp::$isSaveDb) {
                if ($res['data']->num_rows) {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

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
     * @return array
     * [
     *      'status': bool,
     *      'data': mixed,
     *      'error': string
     * ]
     */
    public function select(array $select, bool $isOne = false): array
    {
        if (mmApp::$isSaveDb) {
            $where = '';
            $select = $this->validate($select);
            foreach ($select as $index => $val) {
                if ($where) {
                    $where .= ' AND ';
                }
                $where .= "{$index}={$val}";
            }
            if ($where === '') {
                $where = ' 1 ';
            }
            if ($isOne) {
                $where .= ' LIMIT 1';
            }
            $sql = "SELECT * FROM {$this->getTableName()} WHERE {$where}";
            $data = $this->db->query($sql);
            if ($data) {
                return [
                    'status' => true,
                    'data' => $data
                ];
            }
            return [
                'status' => false,
                'error' => 'Не удалось получить данные'
            ];
        } else {
            $content = $this->getFileData();
            $result = [];
            foreach ($content as $key => $value) {
                $isSelected = false;
                foreach ($select as $index => $val) {
                    if (($value[$index] ?? null) === str_replace('"', '', $val)) {
                        $isSelected = true;
                    } else {
                        $isSelected = false;
                        break;
                    }
                }
                if ($isSelected) {
                    if ($isOne) {
                        return [
                            'status' => true,
                            'data' => $value
                        ];
                    }
                    $result[] = $value;
                }
            }
            if (count($result)) {
                return [
                    'status' => true,
                    'data' => $result
                ];
            }
        }
        return [
            'status' => false,
            'error' => 'Не удалось получить данные'
        ];
    }

    /**
     * Выполнение произвольного запроса к таблице
     *
     * @param string $sql Запрос, который необходимо выполнить
     * @return bool|mysqli_result|null
     * @api
     */
    public function query(string $sql)
    {
        if (mmApp::$isSaveDb && $this->db) {
            return $this->db->query($sql);
        }
        return null;
    }

    /**
     * Выполнение запроса на удаление записи в таблице
     *
     * @param QueryData $queryDelete Данные для удаления записи
     * @return bool|mysqli_result|null
     * @throws Exception
     */
    public function delete(QueryData $queryDelete)
    {
        if ($queryDelete) {
            $delete = $queryDelete->getQuery();
            if (mmApp::$isSaveDb) {
                $where = '';
                foreach ($delete as $index => $value) {
                    if ($where) {
                        $where .= ' AND ';
                    }
                    $where .= "`{$index}`={$value}";
                }
                if ($where) {
                    $sql = "DELETE FROM {$this->getTableName()} WHERE {$where};";
                    return $this->db->query($sql);
                }
            } else {
                $data = $this->getFileData();
                foreach ($delete as $index => $value) {
                    if (isset($data[$value])) {
                        unset($data[$value]);
                        mmApp::saveJson("{$this->tableName}.json", $data);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Декодирование текста(Текст становится приемлемым и безопасным для sql запроса).
     *
     * @param string $text Исходный текст.
     * @return string
     * @api
     */
    public final function escapeString(string $text): string
    {
        if (mmApp::$isSaveDb && $this->db) {
            return $this->db->escapeString($text);
        }
        return $text;
    }

    /**
     * Валидация значений полей для таблицы.
     * @param array $element
     * @return array
     * @api
     */
    public function validate(array $element): array
    {
        if (mmApp::$isSaveDb) {
            $rules = $this->rules;
            if ($rules) {
                foreach ($rules as $rule) {
                    if (!is_array($rule[0])) {
                        $rule[0] = [$rule[0]];
                    }
                    $type = 'number';
                    switch ($rule[1]) {
                        case 'string':
                        case 'text':
                            $type = 'string';
                            break;
                        case 'int':
                        case 'integer':
                        case 'bool':
                            $type = 'number';
                            break;
                    }
                    foreach ($rule[0] as $data) {
                        if ($type === 'string') {
                            if (isset($rule['max'])) {
                                $element[$data] = Text::resize($element[$data], $rule['max']);
                            }
                            $element[$data] = '"' . $this->escapeString($element[$data]) . '"';
                        } else {
                            $element[$data] = (int)$element[$data];
                        }
                    }
                }
            }
        }
        return $element;
    }

    /**
     * Получение всех значений из файла. Актуально если переменная mmApp::$isSaveDb равна false.
     *
     * @return array|mixed
     * @api
     */
    public function getFileData()
    {
        $path = mmApp::$config['json'];
        $file = "{$path}/{$this->tableName}.json";
        if (is_file($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }
}
