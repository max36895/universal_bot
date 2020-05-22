<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 13:47
 */

namespace MM\bot\api;


use MM\bot\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Загрузка изображения для навыка
 * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-upload-docpage/)
 *
 * Class YandexImageRequest
 * @package bot\core\api
 *
 * @property string $skillId: Идентификатор навыка,  необходим для корректного сохранения изображения(Обязательный параметр)
 * @see YandexRequest
 */
class YandexImageRequest extends YandexRequest
{
    /**
     * @const string Адрес, на который будет отправляться запрос
     */
    private const STANDARD_URL = 'https://dialogs.yandex.net/api/v1/';
    public $skillId;

    /**
     * YandexImageRequest constructor.
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
     * Получить адрес для загрузки изображения
     *
     * @return string
     */
    private function getImagesUrl(): string
    {
        return self::STANDARD_URL . 'skills/' . $this->skillId . '/images';
    }

    /**
     * Проверка занятого места
     *
     * Возвращает массив
     * - total - Все доступное место
     * - used - Занятое место
     *
     * @return array|null
     *  - @var int total: Все доступное место
     *  - @var int used: Занятое место
     */
    public function checkOutPlace(): ?array
    {
        $this->request->url = self::STANDARD_URL . 'status';
        $query = $this->call();
        if (isset($query['images']['quota'])) {
            return $query['images']['quota'];
        }
        $this->log('YandexImageRequest::checkOutPlace() Error: Не удалось проверить занятое место!');
        return null;
    }

    /**
     * Загрузка изображения из интернета
     *
     * Возвращает массив
     * - id - Идентификатор изображения
     * - origUrl - Адрес изображения
     *
     * @param $imageUrl - Адрес картинки из интернета
     *
     * @return array|null
     *  - @var string id: Идентификатор изображения
     *  - @var string origUrl: Адрес изображения
     *  - @var int size: Размер изображения
     *  - @var int createdAt: Дата загрузки
     */
    public function downloadImageUrl(string $imageUrl): ?array
    {
        if ($this->skillId) {
            $this->request->url = $this->getImagesUrl();
            $this->request->header[] = Request::HEADER_AP_JSON;
            $this->request->post = ['url' => $imageUrl];
            $query = $this->call();
            if (isset($query['image']['id'])) {
                return $query['image'];
            } else {
                $this->log('YandexImageRequest::downloadImageUrl() Error: Не удалось загрузить изображение с сайта!');
            }
        } else {
            $this->log('YandexImageRequest::downloadImageUrl() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Загрузка изображения из файла
     *
     * Возвращает массив
     * - id - Идентификатор изображения
     * - origUrl - Адрес изображения
     *
     * @param $imageDir - Адрес картинки из интернета
     *
     * @return array|null
     *  - @var string id: Идентификатор изображения
     *  - @var string origUrl: Адрес изображения
     *  - @var int size: Размер изображения
     *  - @var int createdAt: Дата загрузки
     */
    public function downloadImageFile(string $imageDir): ?array
    {
        if ($this->skillId) {
            $this->request->url = $this->getImagesUrl();
            $this->request->header[] = Request::HEADER_FORM_DATA;
            $this->request->attach = $imageDir;
            $query = $this->call();
            if (isset($query['image']['id'])) {
                return $query['image'];
            } else {
                $this->log('YandexImageRequest::downloadImageFile() Error: Не удалось загрузить изображение по пути: ' . $imageDir);
            }
        } else {
            $this->log('YandexImageRequest::downloadImageFile() Error: Не выбран навык!');
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
     * @return null|array[['id' => string, 'origUrl' => string],...]
     *  - @var array
     *      - @var string id: Идентификатор изображения
     *      - @var string origUrl: Адрес изображения
     *      - @var int size: Размер изображения
     *      - @var int createdAt: Дата загрузки
     */
    public function getLoadedImages(): ?array
    {
        if ($this->skillId) {
            $this->request->url = $this->getImagesUrl();
            $query = $this->call();
            return $query['images'] ?? null;
        } else {
            $this->log('YandexImageRequest::getLoadedImages() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Удаление выбранной картинки
     * В случае успеха вернет 'ok'
     *
     * @param $imageId - Идентификатор картинки, которую необходимо удалить.
     *
     * @return null|string
     */
    public function deleteImage(string $imageId): ?string
    {
        if ($this->skillId) {
            if ($imageId) {
                $this->request->url = $this->getImagesUrl() . '/' . $imageId;
                $this->request->customRequest = 'DELETE';
                $query = $this->call();
                if (isset($query['result'])) {
                    return $query['result'];
                } else {
                    $this->log('YandexImageRequest::deleteImage() Error: Не удалось удалить картинку!');
                }
            } else {
                $this->log('YandexImageRequest::deleteImage() Error: Не выбрано изображение!');
            }
        } else {
            $this->log('YandexImageRequest::deleteImage() Error: Не выбран навык!');
        }
        return null;
    }

    /**
     * Удаление всех картинок
     * Если при удалении произошел сбой, то картинка останется.
     * Чтобы точно удалить все картинки лучше использовать грубое удаление
     *
     * Возвращает массив
     * - success - Количество успешно удаленных картинок
     * - fail - Количество неудаленных картинок
     *
     * @return bool
     */
    public function deleteImages(): bool
    {
        if ($this->skillId) {
            $sounds = $this->getLoadedImages();
            if ($sounds) {
                foreach ($sounds as $sound) {
                    $this->deleteImage($sound['id'] ?? null);
                    sleep(3);
                }
            } else {
                $this->log('YandexImageRequest::deleteImages() Error: Не удалось получить загруженные звуки!');
            }
        } else {
            $this->log('YandexImageRequest::deleteImages() Error: Не выбран навык!');
        }
        return false;
    }
}
