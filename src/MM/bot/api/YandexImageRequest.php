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
 * Класс отвечающий за загрузку изображений в навык.
 * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-upload-docpage/) Смотри тут
 *
 * Class YandexImageRequest
 * @package bot\api
 */
class YandexImageRequest extends YandexRequest
{
    /**
     * @const string Адрес, на который будет отправляться запрос
     */
    private const STANDARD_URL = 'https://dialogs.yandex.net/api/v1/';
    /**
     * Идентификатор навыка, необходимый для корректного сохранения изображения (Обязательный параметр)
     * @var string|null $skillId
     * @see YandexRequest Смотри тут
     */
    public $skillId;

    /**
     * YandexImageRequest constructor.
     *
     * @param string|null $oauth Авторизационный токен для загрузки изображений.
     * @param string|null $skillId Идентификатор навыка.
     * @see (https://tech.yandex.ru/dialogs/alice/doc/resource-upload-docpage/) - Документация.
     * @see (https://oauth.yandex.ru/verification_code) - Получение токена.
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
     * Получение адреса для загрузки изображения.
     *
     * @return string
     */
    private function getImagesUrl(): string
    {
        return self::STANDARD_URL . 'skills/' . $this->skillId . '/images';
    }

    /**
     * Проверка занятого места.
     *
     * @return array|null
     * [
     *  - int total: Все доступное место.
     *  - int used: Занятое место.
     * ]
     * @api
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
     * Загрузка изображения из интернета.
     *
     * @param string $imageUrl Адрес изображения из интернета.
     * @return array|null
     * [
     *  - string id: Идентификатор изображения.
     *  - string origUrl: Адрес изображения.
     *  - int size: Размер изображения.
     *  - int createdAt: Дата загрузки.
     * ]
     * @api
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
     * Загрузка изображения из файла.
     *
     * @param string $imageDir Адрес изображения из интернета.
     * @return array|null
     * [
     *  - string id: Идентификатор изображения.
     *  - string origUrl: Адрес изображения.
     *  - int size: Размер изображения.
     *  - int createdAt: Дата загрузки.
     * ]
     * @api
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
     * Просмотр всех загруженных изображений.
     *
     * @return array|null
     * [
     *  [
     *      - string id: Идентификатор изображения.
     *      - string origUrl: Адрес изображения.
     *      - int size: Размер изображения.
     *      - int createdAt: Дата загрузки.
     *  ]
     * ]
     * @api
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
     * Удаление выбранного изображения.
     * В случае успеха вернет 'ok'.
     *
     * @param string $imageId Идентификатор изображения, которую необходимо удалить.
     * @return string|null
     * @api
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
     * Удаление всех изображений.
     * Если при удалении произошел сбой, то изображение останется.
     * Чтобы точно удалить все изображения лучше использовать грубое удаление.
     *
     * @return bool
     * @api
     */
    public function deleteImages(): bool
    {
        if ($this->skillId) {
            $images = $this->getLoadedImages();
            if ($images) {
                foreach ($images as $image) {
                    $this->deleteImage($image['id'] ?? null);
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
