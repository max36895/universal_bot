<?php

namespace MM\bot\api;


use Exception;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отправку запросов на сервер Маруси.
 *
 * Документация по api.
 * @see (https://vk.com/dev/marusia_skill_docs10) Смотри тут
 *
 * Class MarusiaRequest
 * @package bot\api
 */
class MarusiaRequest extends VkRequest
{
    /**
     * MarusiaRequest constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (isset(mmApp::$params['marusia_token'])) {
            $this->initToken(mmApp::$params['marusia_token']);
        }
    }

    /**
     * Получение данные по загрузке изображения на сервер маруси.
     *
     * @return array|null
     * [
     *  - 'picture_upload_link' => string Адрес сервера для загрузки изображения
     * ]
     * @api
     * @throws Exception
     */
    public function marusiaGetPictureUploadLink(): ?array
    {
        return $this->call('marusia.getPictureUploadLink');
    }

    /**
     * Сохранение картинки на сервер Маруси.
     *
     * @param string $photo Фотография.
     * @param string $server Сервер.
     * @param string $hash Хэш.
     * @return array|null
     * [
     *  - 'app_id' => int Идентификатор приложения
     *  - 'photo_id' => int Идентификатор изображения
     * ]
     * @see upload() Смотри тут
     * @api
     * @throws Exception
     */
    public function marusiaSavePicture(string $photo, string $server, string $hash): ?array
    {
        $this->request->post = [
            'photo' => $photo,
            'server' => $server,
            'hash' => $hash
        ];
        return $this->call('marusia.savePicture');
    }

    /**
     * Получение всех загруженных изображений
     * @return array|null
     */
    public function marusiaGetPictures(): ?array
    {
        return $this->call('marusia.getPictures');
    }

    /**
     * Получение данные по загрузке изображения на сервер маруси.
     *
     * @return array|null
     * [
     *  - 'audio_upload_link' => string Адрес сервера для загрузки изображения
     * ]
     * @api
     * @throws Exception
     */
    public function marusiaGetAudioUploadLink(): ?array
    {
        return $this->call('marusia.getAudioUploadLink');
    }

    /**
     * Сохранение аудиио на сервер Маруси.
     *
     * @param array $audio_meta анные полученные после загрузки аудио.
     * @return array|null
     * [
     *  - 'id' => int Идентификатор аудио
     *  - 'title' => string Название аудио
     * ]
     * @see upload() Смотри тут
     * @api
     * @throws Exception
     */
    public function marusiaCreateAudio(array $audio_meta): ?array
    {
        $this->request->post = [
            'audio_meta' => $audio_meta
        ];
        return $this->call('marusia.createAudio');
    }

    /**
     * Сохранение логов.
     *
     * @param string $error Текст ошибки.
     * @throws Exception
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('MarusiaApi.log', $error);
    }
}
