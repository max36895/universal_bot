<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 13.04.2020
 * Time: 13:55
 */

namespace MM\bot\api;


use MM\bot\components\standard\Text;
use MM\bot\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Отправка запросов к viber серверу.
 *
 * Документация по viber Api
 * @see (https://developers.viber.com/docs/api/rest-bot-api/)
 *
 * Class ViberRequest
 * @package bot\core\api
 *
 * @property string $token: Авторизационный токен бота, необходим для отправки данных.
 */
class ViberRequest
{
    const API_ENDPOINT = 'https://chatapi.viber.com/pa/';

    /**
     * @var Request $request : Отправка запросов
     * @see Request
     */
    protected $request;
    protected $error;

    public $token;

    public function __construct()
    {
        $this->request = new Request();
        $this->token = null;
        if (isset(mmApp::$params['viber_token'])) {
            $this->token = mmApp::$params['viber_token'];
        }
    }

    /**
     * Установить токен
     * @param $token
     */
    public function initToken($token): void
    {
        $this->token = $token;
    }

    /**
     * Отправка запросов к viber серверу
     *
     * @param string $method : Название метода
     * @return array|null
     */
    public function call(string $method): ?array
    {
        if ($this->token) {
            if ($method) {
                $this->request->header = [
                    "X-Viber-Auth-Token: {$this->token}"
                ];
                $this->request->post['min_api_version'] = mmApp::$params['viber_api_version'] ?? 2;
                $data = $this->request->send(self::API_ENDPOINT . $method);
                if (isset($data['failed_list']) && count($data['failed_list'])) {
                    $this->error = json_encode($data['failed_list'], JSON_UNESCAPED_UNICODE);
                    $this->log($data['status_message']);
                }
                if ($data['status'] == 0) {
                    return $data;
                }
                if (($data['status_message'] ?? 'ok') !== 'ok') {
                    $this->error = '';
                    $this->log($data['status_message']);
                }
            }
        } else {
            $this->log('Не указан viber токен!');
        }
        return null;
    }

    /**
     * Запрос будет получать сведения о конкретном пользователе Viber на основе его уникального идентификатора.
     * Этот запрос может быть отправлен дважды в течение 12 часов для каждого идентификатора пользователя.
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#get-user-details)
     *
     * @param string $id : Уникальный идентификатор пользователя
     * @return array|null
     *  - @var int status: Результат действия
     *  - @var string status_message: Статус сообщения
     *  - @var int message_token: Уникальный идентификатор сообщения
     *  - @var array user: Информация о пользователе
     *      - @var string id: Уникальный идентификатор пользователя Viber
     *      - @var string name: Имя пользователя Viber
     *      - @var string avatar: URL-адрес аватара пользователя
     *      - @var string country: Код страны пользователя
     *      - @var string language: Язык телефона пользователя. Будет возвращен в соответствии с языком устройства
     *      - @var string primary_device_os: Тип операционной системы и версия основного устройства пользователя.
     *      - @var int api_version: Версия Viber, установленная на основном устройстве пользователя
     *      - @var string viber_version: Версия Viber, установленная на основном устройстве пользователя
     *      - @var int mcc: Мобильный код страны
     *      - @var int mnc: Код мобильной сети
     *      - @var string device_type: Тип устройства пользователя
     */
    public function getUserDetails(string $id)
    {
        $this->request->post = [
            'id' => $id
        ];
        return $this->call('get_user_details');
    }

    /**
     * Отправка сообщения пользователю
     * Отправка сообщения пользователю будет возможна только после того, как пользователь подпишется на бота, отправив ему сообщение.
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#send-message)
     *
     * @param string $receiver : Уникальный идентификатор пользователя Viber
     * @param array|string $sender : Отправитель
     *  - @var string name: Имя отправителя для отображения (Максимум 28 символов)
     *  - @var string avatar: URL-адрес Аватара отправителя (Размер аватара должен быть не более 100 Кб. Рекомендуется 720x720)
     * @param string $text : Текст сообщения
     * @param array $params :
     *  - @var string receiver: Уникальный идентификатор пользователя Viber
     *  - @var string type: Тип сообщения. (Доступные типы сообщений: text, picture, video, file, location, contact, sticker, carousel content и url)
     *  - @var string $sender : Отправитель
     *  - @var string tracking_data: Разрешить учетной записи отслеживать сообщения и ответы пользователя. Отправлено tracking_data значение будет передано обратно с ответом пользователя
     *  - @var string min_api_version: Минимальная версия API, необходимая клиентам для этого сообщения (по умолчанию 1)
     *  - @var string $text : Текст сообщения. (Обязательный параметр)
     *  - @var string media: Url адрес отправляемого контента. Атуально при отправке файлов.
     *  - @var string thumbnail: URL-адрес изображения уменьшенного размера. Актуально при отправке файлов
     *  - @var int size: Размер файла в байтах
     *  - @var int duration: Продолжительность видео или аудио в секундах. Будет отображаться на приемнике
     *  - @var string file_name: Имя файла. Актуально для type = file
     *  - @var array contact: Контакты пользователя. Актуально для type = contact
     *      - @var string name: Имя контактного лица
     *      - @var string phone_number: Номер телефона контактного лица
     *  - @var array location: Координаты местоположения. Актуально для type = location
     *      - @var string lat: Координата lat
     *      - @var string lon: Координата lon
     *  - @var int sticker_id: Уникальный идентификатор стикера Viber. Актуально для type = sticker
     * @return array|null
     */
    public function sendMessage(string $receiver, $sender, string $text, array $params = []): ?array
    {
        $this->request->post['receiver'] = $receiver;
        if (is_array($sender)) {
            $this->request->post['sender'] = $sender;
        } else {
            $this->request->post['sender'] = [
                'name' => $sender
            ];
        }
        $this->request->post['text'] = $text;
        $this->request->post['type'] = 'text';
        if (count($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('send_message');
    }

    /**
     * Установка webhook для vider
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#webhooks)
     *
     * @param string $url : Адресс webhook
     * @param array $params : Дополнительные параметры
     * @return array|null
     */
    public function setWebhook(string $url, array $params = []): ?array
    {
        if ($url) {
            $this->request->post = [
                'url' => $url,
                'event_types' => [
                    'delivered',
                    'seen',
                    'failed',
                    'subscribed',
                    'unsubscribed',
                    'conversation_started'
                ],
                'send_name' => true,
                'send_photo' => true
            ];
        } else {
            $this->request->post = [
                'url' => ''
            ];
        }
        if (count($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('set_webhook');
    }

    /**
     * Отправка карточки пользователю
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#message-types)
     *
     * @param string $receiver : Уникальный идентификатор пользователя Viber
     * @param array $richMedia : Отображаемые данные. Параметр 'Buttons'
     * @param array $params : Дополнительные параметры
     * @return array|null
     * @see sendMessage()
     */
    public function richMedia(string $receiver, array $richMedia, array $params = []): ?array
    {
        $this->request->post = [
            'receiver' => $receiver,
            'type' => 'rich_media',
            'rich_media' => [
                'Type' => 'rich_media',
                'ButtonsGroupColumns' => 6,
                'ButtonsGroupRows' => count($richMedia),
                'BgColor' => '#FFFFFF',
                'Buttons' => $richMedia
            ]
        ];
        if (count($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('send_message');
    }

    /**
     * @param string $receiver : Уникальный идентификатор пользователя Viber
     * @param string $file : Ссылка на файл
     * @param array $params Дополнительные параметры
     * @return array|null
     * @see sendMessage()
     */
    public function sendFile(string $receiver, string $file, array $params = [])
    {
        $this->request->post = [
            'receiver' => $receiver
        ];
        if (Text::isSayText(['http:\/\/', 'https:\/\/'], $file)) {
            $this->request->post['type'] = 'file';
            $this->request->post['media'] = $file;
            $this->request->post['size'] = 10000;
            $this->request->post['file_name'] = Text::resize($file, 150);
            if (count($params)) {
                $this->request->post = mmApp::arrayMerge($this->request->post, $params);
            }
            return $this->call('send_message');
        }
        return null;
    }

    /**
     * Запись логов
     *
     * @param string $error
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('viberApi.log', $error);
    }
}
