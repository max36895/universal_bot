<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\api;


use MM\bot\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за загрузку аудиофайлов в навык
 * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-sounds-upload-docpage/) Смотри тут
 *
 * Class YandexSoundRequest
 * @package bot\api
 */
class YandexSoundRequest extends YandexRequest
{
    /**
     * @const string Адрес, на который будет отправляться запрос.
     */
    private const STANDARD_URL = 'https://dialogs.yandex.net/api/v1/';
    /**
     * Идентификатор навыка, необходимый для корректного сохранения аудиофайла (Обязательный параметр).
     * @var string|null $skillId
     * @see YandexRequest Смотри тут
     */
    public $skillId;

    /**
     * YandexSoundRequest constructor.
     *
     * @param string|null $oauth Авторизационный токен для загрузки аудиофайлов.
     * @param string|null $skillId Идентификатор навыка.
     * @see (https://tech.yandex.ru/dialogs/alice/doc/resource-upload-docpage/) - Документация
     * @see (https://oauth.yandex.ru/verification_code) - Получение токена
     */
    public function __construct(?string $oauth = null, ?string $skillId = null)
    {
        if ($oauth === null) {
            $oauth = mmApp::$params['yandex_token'] ?? null;
        }
        if ($skillId === null) {
            $skillId = mmApp::$params['app_id'] ?? null;
        }
        $this->skillId = $skillId;
        $this->request->url = self::STANDARD_URL;
        parent::__construct($oauth);
    }

    /**
     * Получение адреса для загрузки аудиофайлов.
     *
     * @return string
     * @api
     */
    private function getSoundsUrl(): string
    {
        return self::STANDARD_URL . 'skills/' . $this->skillId . '/sounds';
    }

    /**
     * Проверить занятое место.
     *
     * Для каждого аккаунта на Яндексе действует лимит на загрузку аудиофайлов — вы можете хранить на Диалогах не больше 1 ГБ файлов. Обратите внимание, лимит учитывает размер сжатых аудиофайлов, а не размер оригиналов. Диалоги конвертируют загруженные аудиофайлы в формат OPUS и обрезают их до 120 секунд — размер этих файлов и будет учитываться в лимите.
     *
     * @return array|null
     * [
     * - int total: Все доступное место.
     * - int used: Занятое место.
     * ]
     * @api
     */
    public function checkOutPlace(): ?array
    {
        $this->request->url = self::STANDARD_URL . 'status';
        $query = $this->call();
        if (isset($query['sounds']['quota'])) {
            return $query['sounds']['quota'];
        }
        $this->log('YandexSoundRequest::checkOutPlace() Error: Не удалось проверить занятое место!');

        return null;
    }

    /**
     * Загрузить аудиофайл.
     *
     * @param string|null $soundDir Расположение аудиофайла на сервере.
     *
     * @return array|null
     * [
     *  - string id: Идентификатор аудиофайла.
     *  - string skillId: Идентификатор навыка.
     *  - int|null size: Размер файла.
     *  - string originalName: Название загружаемого файла.
     *  - string createdAt: Дата создания файла.
     *  - bool isProcessed: Флаг готовности файла.
     *  - string|null error: Текст ошибки.
     * ]
     * @api
     */
    public function downloadSoundFile(string $soundDir): ?array
    {
        if ($this->skillId) {
            $this->request->url = $this->getSoundsUrl();
            $this->request->header[] = Request::HEADER_FORM_DATA;
            $this->request->attach = $soundDir;
            $query = $this->call();
            if (isset($query['sound']['id'])) {
                return $query['sound'];
            } else {
                $this->log('YandexSoundRequest::downloadSoundFile() Error: Не удалось загрузить изображение по пути: ' . $soundDir);
            }
        } else {
            $this->log('YandexSoundRequest::downloadSoundFile() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Просмотр всех загруженных аудиофайлов.
     *
     * @return array|null
     * [
     *  [
     *      - string id: Идентификатор аудиофайла.
     *      - string skillId: Идентификатор навыка.
     *      - int|null size: Размер файла.
     *      - string originalName: Название загружаемого файла.
     *      - string createdAt: Дата создания файла.
     *      - bool isProcessed: Флаг готовности файла.
     *      - string|null error: Текст ошибки.
     *  ]
     * ]
     * @api
     */
    public function getLoadedSounds(): ?array
    {
        if ($this->skillId) {
            $this->request->url = $this->getSoundsUrl();
            $query = $this->call();
            return $query['sounds'] ?? null;
        } else {
            $this->log('YandexSoundRequest::getLoadedSounds() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Удаление выбранного аудиофайла.
     * В случае успеха вернет 'ok'.
     *
     * @param string $soundId Идентификатор аудиофайла, который необходимо удалить.
     *
     * @return string|null
     * @api
     */
    public function deleteSound(string $soundId): ?string
    {
        if ($this->skillId) {
            if ($soundId) {
                $this->request->url = $this->getSoundsUrl() . '/' . $soundId;
                $this->request->customRequest = 'DELETE';
                $query = $this->call();
                if (isset($query['result'])) {
                    return $query['result'];
                } else {
                    $this->log('YandexSoundRequest::deleteSound() Error: Не удалось удалить картинку!');
                }
            } else {
                $this->log('YandexSoundRequest::deleteSound() Error: Не выбрано изображение!');
            }
        } else {
            $this->log('YandexSoundRequest::deleteSound() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Удаление всех аудиофайла.
     * Если при удалении произошел сбой, то аудиофайл останется.
     * Чтобы точно удалить все аудиофайлы лучше использовать грубое удаление.
     *
     * @return bool
     * @api
     */
    public function deleteSounds(): bool
    {
        if ($this->skillId) {
            $sounds = $this->getLoadedSounds();
            if ($sounds) {
                foreach ($sounds as $sound) {
                    $this->deleteSound($sound['id'] ?? null);
                    sleep(3);
                }
            } else {
                $this->log('YandexSoundRequest::deleteSounds() Error: Не удалось получить загруженные звуки!');
            }
        } else {
            $this->log('YandexSoundRequest::deleteSounds() Error: Не выбран навык!');
        }
        return false;
    }
}
