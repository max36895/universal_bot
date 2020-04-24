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
 * Отправка запросов на Yandex сервер
 *
 * Class YandexRequest
 * @package bot\core\api
 */
class YandexRequest
{
    /**
     * @var Request $request Отправка запроса
     * @see Request
     */
    protected $request;

    /**
     * Авторизационный токен
     * @var string
     * О том как получить авторизационный токен сказано тут:
     * @see (https://yandex.ru/dev/dialogs/alice/doc/resource-upload-docpage/#http-images-load__auth)
     */
    private $oauth;
    protected $error;

    /**
     * YandexRequest constructor.
     * @param null $oauth :  Авторизационный токен для загрузки данных
     */
    public function __construct($oauth = null)
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
     * Установка и инициализация токена
     *
     * @param string|null $oauth : Авторизационный токен для загрузки данных
     */
    public function setOAuth($oauth): void
    {
        $this->oauth = $oauth;
        if ($this->request->header) {
            $this->request->header = ['Authorization: OAuth ' . $this->oauth];
        }
    }

    /**
     * Отправка запроса для обработки данных
     *
     * @param string|null $url : Адрес запроса
     * @return mixed
     */
    public function call(?string $url = null)
    {
        $data = $this->request->send($url);
        if ($data['status']) {
            if (isset($data['error'])) {
                $this->error = json_encode($data['error'], JSON_UNESCAPED_UNICODE);
            }
            return $data['data'];
        }
        $this->log($data['err']);
        return null;
    }

    /**
     * Сохранение логов
     *
     * @param string $error
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('YandexApi.log', $error);
    }
}
