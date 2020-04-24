<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 13:47
 */

namespace MM\bot\core\api;


use MM\bot\core\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Загрузка звуков для навыка
 * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-sounds-upload-docpage/)
 *
 * Class YandexSoundRequest
 * @package bot\core\api
 *
 * @property string $skillId: Идентификатор навыка,  необходим для корректного сохранения изображения(Обязательный параметр)
 * @see YandexRequest
 */
class YandexSoundRequest extends YandexRequest
{
    /**
     * @const string Адрес, на который будет отправляться запрос
     */
    private const STANDARD_URL = 'https://dialogs.yandex.net/api/v1/';
    public $skillId;

    /**
     * YandexSoundRequest constructor.
     *
     * @param string|null $oauth : Авторизационный токен для загрузки изображений
     * @param string|null $skillId : Идентификатор навыка
     * @see (https://tech.yandex.ru/dialogs/alice/doc/resource-upload-docpage/) - Документация
     * @see (https://oauth.yandex.ru/verification_code) - Получение токена
     */
    public function __construct(?string $oauth = null, ?string $skillId = null)
    {
        if ($oauth == null) {
            $oauth = mmApp::$params['yandex_token'] ?? null;
        }
        if ($skillId == null) {
            $skillId = mmApp::$params['app_id'] ?? null;
        }
        $this->skillId = $skillId;
        $this->request->url = self::STANDARD_URL;
        parent::__construct($oauth);
    }

    /**
     * Получить адрес для загрузки звуков
     *
     * @return string
     */
    private function getSoundsUrl(): string
    {
        return self::STANDARD_URL . 'skills/' . $this->skillId . '/sounds';
    }

    /**
     * Проверить занятое место
     *
     * Для каждого аккаунта на Яндексе действует лимит на загрузку аудиофайлов — вы можете хранить на Диалогах не больше 1 ГБ файлов. Обратите внимание, лимит учитывает размер сжатых аудиофайлов, а не размер оригиналов. Диалоги конвертируют загруженные аудиофайлы в формат OPUS и обрезают их до 120 секунд — размер этих файлов и будет учитываться в лимите.
     *
     * Вернет массив
     * - total - Все доступное место
     * - used - Занятое место
     *
     * @return array|null
     * - @var int total: Все доступное место
     * - @var int used: Занятое место
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
     * Загрузить аудиофайл
     *
     * Возвращает массив
     * - id - Идентификатор изображения
     * - origUrl - Адрес изображения.
     *
     * @param $soundDir - Расположение картинки на сервере
     *
     * @return array|null
     *  - @var string id: Идентификатор аудиофайла
     *  - @var string skillId: Идентификатор навыка
     *  - @var int|null size: Размер файла
     *  - @var string originalName: Название загружаемого файла
     *  - @var string createdAt: Дата создания файла
     *  - @var bool isProcessed: Флаг готовности файла
     *  - @var string|null error: Текст ошибки
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
     * Просмотр всех загруженных изображений
     *
     * Вернет массив из массива изображений
     * - id - Идентификатор изображения
     * - origUrl - Адрес изображения.
     *
     * @return array|null
     *  - @var array
     *      - @var string id: Идентификатор аудиофайла
     *      - @var string skillId: Идентификатор навыка
     *      - @var int|null size: Размер файла
     *      - @var string originalName: Название загружаемого файла
     *      - @var string createdAt: Дата создания файла
     *      - @var bool isProcessed: Флаг готовности файла
     *      - @var string|null error: Текст ошибки
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
     * Удаление выбранной картинки
     * В случае успеха вернет 'ok'
     *
     * @param $soundId - Идентификатор звука, который необходимо удалить.
     *
     * @return string|null
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
     * Удаление всех звуков
     * Если при удалении произошел сбой, то картинка останется.
     * Чтобы точно удалить все картинки лучше использовать грубое удаление
     *
     * Возвращает массив
     * - success - Количество успешно удаленных картинок
     * - fail - Количество неудаленных картинок
     *
     * @return bool
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
