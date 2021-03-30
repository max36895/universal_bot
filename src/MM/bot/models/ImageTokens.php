<?php

namespace MM\bot\models;

use Exception;
use MM\bot\api\TelegramRequest;
use MM\bot\api\VkRequest;
use MM\bot\api\YandexImageRequest;
use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;
use MM\bot\models\db\Model;
use mysqli_result;

/**
 * Class ImageTokens
 * @package bot\models
 *
 * Модель для взаимодействия со всеми изображениями.
 */
class ImageTokens extends Model
{
    const TABLE_NAME = 'ImageTokens';
    const T_ALISA = 0;
    const T_VK = 1;
    const T_TELEGRAM = 2;
    const T_MARUSIA = 3;

    /**
     * Идентификатор/токен изображения.
     * @var string|null $imageToken
     */
    public $imageToken;
    /**
     * Расположение изображения (url/директория).
     * @var string|null $path
     */
    public $path;
    /**
     * Тип приложения, для которого загружена картинка.
     * @var string|int $type
     */
    public $type;
    /**
     * Описание изображения (Не обязательное поле).
     * @var string|null $caption
     */
    public $caption;

    /**
     * ImageTokens constructor.
     */
    public function __construct()
    {
        $this->imageToken = null;
        $this->path = null;
        $this->type = self::T_ALISA;
        $this->caption = null;
        parent::__construct();
    }

    /**
     * Создание таблицы бд для хранения загруженных картинок.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function createTable()
    {
        if (mmApp::$isSaveDb) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `imageToken` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
  `path` VARCHAR(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` INT(3) NOT NULL,
  PRIMARY KEY (`imageToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            return $this->query($sql);
        }
        return null;
    }

    /**
     * Удаление таблицы бд для хранения загруженных картинок.
     *
     * @return bool|mysqli_result|null
     * @api
     */
    public function dropTable()
    {
        if (mmApp::$isSaveDb) {
            return $this->query("DROP TABLE IF EXISTS `{$this->tableName()}`;");
        }
        return null;
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
            [['imageToken', 'path'], 'string', 'max' => 150],
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
            'imageToken' => 'ID',
            'path' => 'Image path',
            'type' => 'Type'
        ];
    }

    /**
     * Получение идентификатора/токена изображения.
     *
     * @return string|null
     * @throws Exception
     * @api
     */
    public function getToken(): ?string
    {
        $query = [
            'path' => $this->path,
            'type' => $this->type
        ];
        switch ($this->type) {
            case self::T_ALISA:
                if ($this->whereOne($query)) {
                    return $this->imageToken;
                } else {
                    $yImage = new YandexImageRequest(mmApp::$params['yandex_token'] ?? null, mmApp::$params['app_id'] ?? null);
                    if (Text::isSayText(['http\:\/\/', 'https\:\/\/'], $this->path)) {
                        $res = $yImage->downloadImageUrl($this->path);
                    } else {
                        $res = $yImage->downloadImageFile($this->path);
                    }
                    if ($res) {
                        $this->imageToken = $res['id'];
                        if ($this->save(true)) {
                            return $this->imageToken;
                        }
                    }
                }
                break;

            case self::T_VK:
            case self::T_MARUSIA: // TODO не понятно как получить токен, возможно также и в вк
                $query['type'] = self::T_VK;
                if ($this->whereOne($query)) {
                    return $this->imageToken;
                } else {
                    $vkApi = new VkRequest();
                    $uploadServerResponse = $vkApi->photosGetMessagesUploadServer(mmApp::$params['user_id']);
                    if ($uploadServerResponse) {
                        $uploadResponse = $vkApi->upload($uploadServerResponse['upload_url'], $this->path);
                        if ($uploadResponse) {
                            $photo = $vkApi->photosSaveMessagesPhoto($uploadResponse['photo'], $uploadResponse['server'], $uploadResponse['hash']);
                            if ($photo) {
                                $this->imageToken = "photo{$photo['owner_id']}_{$photo['id']}";
                                if ($this->save(true)) {
                                    return $this->imageToken;
                                }
                            }
                        }
                    }
                }
                break;

            case self::T_TELEGRAM:
                $telegramApi = new TelegramRequest();
                if ($this->whereOne($query)) {
                    $telegramApi->sendPhoto(mmApp::$params['user_id'], $this->imageToken, $this->caption);
                    return $this->imageToken;
                } else {
                    $photo = $telegramApi->sendPhoto(mmApp::$params['user_id'], $this->path, $this->caption);
                    if ($photo && $photo['ok']) {
                        if (isset($photo['result']['photo']['file_id'])) {
                            $this->imageToken = $photo['result']['photo']['file_id'];
                            if ($this->save(true)) {
                                return $this->imageToken;
                            }
                        }
                    }

                }
                break;
        }
        return null;
    }
}
