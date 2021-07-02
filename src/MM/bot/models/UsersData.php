<?php

namespace MM\bot\models;

use MM\bot\core\mmApp;
use MM\bot\models\db\Model;
use mysqli_result;

/**
 * Class UsersData
 * @package bot\models
 *
 * Модель для взаимодействия со всеми пользовательскими данными.
 */
class UsersData extends Model
{
    const TABLE_NAME = 'UsersData';
    const T_ALISA = 0;
    const T_VK = 1;
    const T_TELEGRAM = 2;
    const T_VIBER = 3;
    const T_MARUSIA = 4;
    const T_SMART_APP = 5;

    const T_USER_APP = 512;

    /**
     * Идентификатор пользователя (Уникальный ключ).
     * @var string|null $userId
     */
    public $userId;
    /**
     * Meta данные пользователя.
     * @var string|array|null $meta
     */
    public $meta;
    /**
     * Пользовательские данные.
     * @var string|array|null $data
     */
    public $data;
    /**
     * Тип записи (0 - Алиса; 1 - Vk; 2 - Telegram).
     * @var int $type
     */
    public $type;

    /**
     * UsersData constructor.
     */
    public function __construct()
    {
        $this->userId = null;
        $this->meta = null;
        $this->data = null;
        $this->type = self::T_ALISA;
        parent::__construct();
    }

    /**
     * Создание таблицы бд для хранения пользовательских данных.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
 `userId` VARCHAR(250) COLLATE utf8_unicode_ci NOT NULL,
 `meta` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
 `data` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
 `type` INT(3) DEFAULT 0,
 PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        return $this->query($sql);
    }

    /**
     * Удаление таблицы бд для хранения пользовательских данных.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function dropTable()
    {
        return $this->query("DROP TABLE IF EXISTS `{$this->tableName()}`;");
    }

    /**
     * Название таблицы/файла с данными.
     *
     * @return string
     * @api
     */
    public function tableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Основные правила для полей.
     *
     * @return array
     * @api
     */
    public function rules(): array
    {
        return [
            [['userId'], 'string', 'max' => 250],
            [['meta', 'data'], 'text'],
            ['type', 'integer']
        ];
    }

    /**
     * Название атрибутов таблицы.
     *
     * @return array
     * @api
     */
    public function attributeLabels(): array
    {
        return [
            'userId' => 'ID',
            'meta' => 'User meta data',
            'data' => 'User Data',
            'type' => 'Type'
        ];
    }

    /**
     * Выполнение запроса на поиск одного значения.
     * В случае успешного поиска вернет true.
     *
     * @return bool
     * @api
     */
    public function getOne(): bool
    {
        $query = $this->selectOne();
        if ($query && $query->status) {
            $data = $this->dbController->getValue($query);
            $this->init($data);
            return true;
        }
        return false;
    }

    /**
     * Валидация значений.
     * @api
     */
    public function validate(): void
    {
        if (mmApp::$isSaveDb) {
            if (is_array($this->meta)) {
                $this->meta = json_encode($this->meta, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($this->data)) {
                $this->data = json_encode($this->data, JSON_UNESCAPED_UNICODE);
            }
        }
        parent::validate();
    }

    /**
     * Инициализация параметров.
     *
     * @param array $data Массив с данными.
     * @api
     */
    public function init(array $data): void
    {
        parent::init($data);
        if (mmApp::$isSaveDb) {
            if (!is_array($this->meta)) {
                $this->meta = json_decode($this->meta, true);
            }
            if (!is_array($this->data)) {
                $this->data = json_decode($this->data, true);
            }
        }
    }
}
