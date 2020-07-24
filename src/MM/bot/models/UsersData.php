<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 10:40
 */

namespace MM\bot\models;

use MM\bot\models\db\Model;
use mysqli_result;

/**
 * Class UsersData
 * @package bot\models
 *
 * Модель для взаимодействия со всеми пользовательскими данными
 *
 * @property string $userId: Идентификатор пользователя (Уникальный ключ)
 * @property string|array $meta: Meta данные пользователя
 * @property string|array $data: Пользовательские данные
 * @property int $type: Тип записи (0 - Алиса; 1 - Vk; 2 - Telegram)
 */
class UsersData extends Model
{
    const TABLE_NAME = 'UsersData';
    const T_ALISA = 0;
    const T_VK = 1;
    const T_TELEGRAM = 2;
    const T_VIBER = 3;
    const T_MARUSIA = 4;

    const T_USER_APP = 512;

    public $userId;
    public $meta;
    public $data;
    public $type;

    /**
     * UsersData constructor.
     */
    public function __construct()
    {
        $this->userId = null;
        $this->meta = null;
        $this->data = null;
        parent::__construct();
    }

    /**
     * Создание таблицы бд для хранения пользовательских данных
     *
     * @return bool|mysqli_result|null
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
     * Удаление таблицы бд для хранения пользовательских данных
     *
     * @return bool|mysqli_result|null
     */
    public function dropTable()
    {
        return $this->query("DROP TABLE IF EXISTS `{$this->tableName()}`;");
    }

    /**
     * Название таблицы/файла с данными
     *
     * @return string
     */
    public function tableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * Основные правила для полей
     *
     * @return array
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
     * Название атрибутов таблицы
     *
     * @return array
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
     * Выполнить запрос на поиск одного значения.
     * В случае успешного поиска вернет true
     *
     * @return bool
     */
    public function getOne(): bool
    {
        $one = $this->selectOne();
        if (IS_SAVE_DB) {
            if ($one && $one->num_rows) {
                $this->init($one->fetch_array(MYSQLI_NUM));
                $one->free_result();
                return true;
            }
        } else {
            if ($one) {
                $this->init($one);
                return true;
            }
        }
        return false;
    }

    /**
     * Валидация значений
     */
    public function validate(): void
    {
        if (IS_SAVE_DB) {
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
     * Инициализация параметров
     *
     * @param array $data : Массив с данными
     */
    public function init(array $data): void
    {
        parent::init($data);
        if (IS_SAVE_DB) {
            if (!is_array($this->meta)) {
                $this->meta = json_decode($this->meta, true);
            }
            if (!is_array($this->data)) {
                $this->data = json_decode($this->data, true);
            }
        }
    }
}
