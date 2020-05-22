<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 10:40
 */

namespace MM\bot\models;

use MM\bot\components\standard\Text;
use MM\bot\api\TelegramRequest;
use MM\bot\api\VkRequest;
use MM\bot\api\YandexSoundRequest;
use MM\bot\core\mmApp;
use MM\bot\models\db\Model;
use mysqli_result;

/**
 * Class SoundTokens
 * @package bot\models
 *
 * Модель для взаимодействия со всеми звуками
 *
 * @property string $soundToken: Идентификатор/токен мелодии
 * @property string $path: Расположение звукового файла(url|/директория)
 * @property string $type: Тип приложения, для которого загружена мелодия
 * @property bool $isAttachContent: True если передается содержимое файла. По умолчанию: false
 */
class SoundTokens extends Model
{
    const TABLE_NAME = 'SoundTokens';
    const T_ALISA = 0;
    const T_VK = 1;
    const T_TELEGRAM = 2;

    public $soundToken;
    public $path;
    public $type;
    public $isAttachContent;

    /**
     * SoundTokens constructor.
     */
    public function __construct()
    {
        $this->soundToken = null;
        $this->path = null;
        $this->type = self::T_ALISA;
        $this->isAttachContent = false;
        parent::__construct();
    }

    /**
     * Создание таблицы бд для хранения загруженных звуков
     *
     * @return bool|mysqli_result|null
     */
    public function createTable()
    {
        if (IS_SAVE_DB) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
 `soundToken` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
 `path` VARCHAR(150) COLLATE utf8_unicode_ci DEFAULT NULL,
 `type` INT(3) NOT NULL,
 PRIMARY KEY (`soundToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            return $this->query($sql);
        }
        return null;
    }

    /**
     * Удаление таблицы бд для хранения загруженных звуков
     *
     * @return bool|mysqli_result|null
     */
    public function dropTable()
    {
        if (IS_SAVE_DB) {
            return $this->query("DROP TABLE IF EXISTS `{$this->tableName()}`;");
        }
        return null;
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
            [['soundToken', 'path'], 'string', 'max' => 150],
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
            'soundToken' => 'ID',
            'path' => 'Sound path',
            'type' => 'Type'
        ];
    }

    /**
     * Получить идентификатор/токен мелодии
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        switch ($this->type) {
            case self::T_ALISA:
                if ($this->whereOne("`path`=\"{$this->path}\" AND `type`=" . self::T_ALISA)) {
                    return $this->soundToken;
                } else {
                    $yImage = new YandexSoundRequest(mmApp::$params['yandex_token'] ?? null, mmApp::$params['app_id'] ?? null);
                    if (Text::isSayText(['http\:\/\/', 'https\:\/\/'], $this->path)) {
                        mmApp::saveLog('mSoundTokens.log', 'Нельзя отправить звук в навык для Алисы через url!');
                        return null;
                    } else {
                        $res = $yImage->downloadSoundFile($this->path);
                    }
                    if ($res) {
                        $this->soundToken = $res['id'];
                        $status = $this->save();
                        if ($status) {
                            return $this->soundToken;
                        }
                    }
                }
                break;
            case self::T_VK:
                if ($this->whereOne("`path`=\"{$this->path}\" AND `type`=" . self::T_VK)) {
                    return $this->soundToken;
                } else {
                    $vkApi = new VkRequest();
                    $uploadServerResponse = $vkApi->docsGetMessagesUploadServer(mmApp::$params['user_id'], 'audio_message');
                    if ($uploadServerResponse) {
                        $uploadResponse = $vkApi->upload($uploadServerResponse['upload_url'], $this->path);
                        if ($uploadResponse) {
                            $doc = $vkApi->docsSave($uploadResponse['photo'], 'Voice message');
                            if ($doc) {
                                $this->soundToken = "doc{$doc['file']['owner_id']}_{$doc['file']['id']}";
                                $status = $this->save(true);
                                if ($status) {
                                    return $this->soundToken;
                                }
                            }
                        }
                    }
                }
                break;
            case self::T_TELEGRAM:
                $telegramApi = new TelegramRequest();
                if ($this->whereOne("`path`=\"{$this->path}\" AND `type`=" . self::T_TELEGRAM)) {
                    $telegramApi->sendAudio(mmApp::$params['user_id'], $this->soundToken);
                    return $this->soundToken;
                } else {
                    $sound = $telegramApi->sendAudio(mmApp::$params['user_id'], $this->path);
                    if ($sound) {
                        if (isset($sound['audio']['file_id'])) {
                            $this->soundToken = $sound['audio']['file_id'];
                            $status = $this->save(true);
                            if ($status) {
                                return $this->soundToken;
                            }
                        }
                    }

                }
                break;
        }
        return null;
    }
}
