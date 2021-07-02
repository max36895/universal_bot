<?php

namespace MM\bot\api;


use Exception;
use MM\bot\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отправку запросов на Yandex сервер.
 *
 * Class YandexRequest
 * @package bot\core\api
 */
class YandexRequest
{
    /**
     * Отправка запроса.
     * @var Request $request
     * @see Request Смотри тут
     */
    protected $request;

    /**
     * Авторизационный токен.
     * @var string|null $oauth
     * О том как получить авторизационный токен сказано тут:
     * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-upload-docpage/#http-images-load__auth) Смотри тут
     */
    protected $oauth;
    /**
     * Текст с ошибкой
     * @var string|null $error
     */
    protected $error;

    /**
     * YandexRequest constructor.
     * @param string|null $oauth Авторизационный токен для загрузки данных.
     */
    public function __construct(?string $oauth = null)
    {
        if ($oauth) {
            $this->setOAuth($oauth);
        } else {
            $this->setOAuth(mmApp::$params['yandex_token'] ?? null);
        }
        $this->request = new Request();
        $this->request->maxTimeQuery = 1500;
    }

    /**
     * Установка и инициализация токена.
     *
     * @param string|null $oauth Авторизационный токен для загрузки данных.
     * @api
     */
    public function setOAuth(?string $oauth): void
    {
        $this->oauth = $oauth;
        if ($this->request->header) {
            $this->request->header = ['Authorization: OAuth ' . $this->oauth];
        }
    }

    /**
     * Отправка запроса для обработки данных.
     *
     * @param string|null $url Адрес запроса.
     * @return mixed
     * @api
     * @throws Exception
     */
    public function call(?string $url = null)
    {
        $data = $this->request->send($url);
        if ($data['status']) {
            if (isset($data['data']['error'])) {
                $this->error = json_encode($data['data']['error'], JSON_UNESCAPED_UNICODE);
            }
            return $data['data'];
        }
        $this->log($data['err']);
        return null;
    }

    /**
     * Сохранение логов
     *
     * @param string $error Текст ошибки
     * @throws Exception
     * @api
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('YandexApi.log', $error);
    }
}
