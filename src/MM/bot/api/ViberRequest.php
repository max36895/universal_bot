<?php

namespace MM\bot\api;


use Exception;
use MM\bot\api\request\Request;
use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отправку запросов на viber сервер.
 *
 * Документация по viber api.
 * @see (https://developers.viber.com/docs/api/rest-bot-api/) Смотри тут
 *
 * Class ViberRequest
 * @package bot\api
 */
class ViberRequest
{
    /**
     * @const string: Адрес, на который отправляться запрос.
     */
    const API_ENDPOINT = 'https://chatapi.viber.com/pa/';

    /**
     * Отправка запросов.
     * @var Request $request
     * @see Request Смотри тут
     */
    protected $request;
    /**
     * Ошибки при выполнении.
     * @var string $error
     */
    protected $error;

    /**
     * Авторизационный токен бота, необходимый для отправки данных.
     * @var string|null $token
     */
    public $token;

    /**
     * ViberRequest constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->token = null;
        if (isset(mmApp::$params['viber_token'])) {
            $this->initToken(mmApp::$params['viber_token']);
        }
    }

    /**
     * Установить токен.
     *
     * @param string $token Токен необходимый для отправки данных на сервер.
     * @api
     */
    public function initToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Отвечает за отправку запросов на viber сервер.
     *
     * @param string $method Название метода.
     * @return array|null
     * @api
     * @throws Exception
     */
    public function call(string $method): ?array
    {
        if ($this->token) {
            if ($method) {
                $this->request->header = [
                    "X-Viber-Auth-Token: {$this->token}"
                ];
                $this->request->post['min_api_version'] = mmApp::$params['viber_api_version'] ?? 2;
                $data = $this->request->send(self::API_ENDPOINT . $method)['data'] ?? [];
                if (isset($data['failed_list']) && !empty($data['failed_list'])) {
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
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#get-user-details) Смотри тут
     *
     * @param string $id Уникальный идентификатор пользователя.
     * @return array|null
     * [
     *  - int status: Результат действия.
     *  - string status_message: Статус сообщения.
     *  - int message_token: Уникальный идентификатор сообщения.
     *  - array user: Информация о пользователе.
     *  [
     *      - string id: Уникальный идентификатор пользователя Viber.
     *      - string name: Имя пользователя Viber.
     *      - string avatar: URL-адрес аватара пользователя.
     *      - string country: Код страны пользователя.
     *      - string language: Язык телефона пользователя. Будет возвращен в соответствии с языком устройства.
     *      - string primary_device_os: Тип операционной системы и версия основного устройства пользователя.
     *      - int api_version: Версия Viber, установленная на основном устройстве пользователя.
     *      - string viber_version: Версия Viber, установленная на основном устройстве пользователя.
     *      - int mcc: Мобильный код страны.
     *      - int mnc: Код мобильной сети.
     *      - string device_type: Тип устройства пользователя.
     *  ]
     * ]
     * @api
     * @throws Exception
     */
    public function getUserDetails(string $id)
    {
        $this->request->post = [
            'id' => $id
        ];
        return $this->call('get_user_details');
    }

    /**
     * Отправка сообщения пользователю.
     * Отправка сообщения пользователю будет возможна только после того, как пользователь подпишется на бота, отправив ему сообщение.
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#send-message) Смотри тут
     *
     * @param string $receiver Уникальный идентификатор пользователя Viber.
     * @param array|string $sender Отправитель:
     * [
     *  - string name: Имя отправителя для отображения (Максимум 28 символов).
     *  - string avatar: URL-адрес Аватара отправителя (Размер аватара должен быть не более 100 Кб. Рекомендуется 720x720).
     * ]
     * @param string $text Текст сообщения.
     * @param array $params Дополнительные параметры:
     * [
     *  - string receiver: Уникальный идентификатор пользователя Viber.
     *  - string type: Тип сообщения. (Доступные типы сообщений: text, picture, video, file, location, contact, sticker, carousel content и url).
     *  - string $sender Отправитель.
     *  - string tracking_data: Разрешить учетной записи отслеживать сообщения и ответы пользователя. Отправлено tracking_data значение будет передано обратно с ответом пользователя.
     *  - string min_api_version: Минимальная версия API, необходимая клиентам для этого сообщения (по умолчанию 1).
     *  - string $text Текст сообщения. (Обязательный параметр).
     *  - string media: Url адрес отправляемого контента. Актуально при отправке файлов.
     *  - string thumbnail: URL-адрес изображения уменьшенного размера. Актуально при отправке файлов.
     *  - int size: Размер файла в байтах.
     *  - int duration: Продолжительность видео или аудио в секундах. Будет отображаться на приемнике.
     *  - string file_name: Имя файла. Актуально для type = file.
     *  - array contact: Контакты пользователя. Актуально для type = contact.
     *  [
     *      - string name: Имя контактного лица.
     *      - string phone_number: Номер телефона контактного лица.
     *  ]
     *  - array location: Координаты местоположения. Актуально для type = location.
     *  [
     *      - string lat: Координата lat.
     *      - string lon: Координата lon.
     *  ]
     *  - int sticker_id: Уникальный идентификатор стикера Viber. Актуально для type = sticker.
     * ]
     * @return array|null
     * @api
     * @throws Exception
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
        if (!empty($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('send_message');
    }

    /**
     * Установка webhook для vider.
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#webhooks) Смотри тут
     *
     * @param string $url Адрес webhook`а.
     * @param array $params Дополнительные параметры.
     * @return array|null
     * @api
     * @throws Exception
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
        if (!empty($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('set_webhook');
    }

    /**
     * Отправка карточки пользователю.
     * @see (https://developers.viber.com/docs/api/rest-bot-api/#message-types) Смотри тут
     *
     * @param string $receiver Уникальный идентификатор пользователя Viber.
     * @param array $richMedia Отображаемые данные. Параметр 'Buttons'.
     * @param array $params Дополнительные параметры.
     * @return array|null
     * @see sendMessage() Смотри тут
     * @api
     * @throws Exception
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
        if (!empty($params)) {
            $this->request->post = mmApp::arrayMerge($this->request->post, $params);
        }
        return $this->call('send_message');
    }

    /**
     * Отправить файл на сервер.
     *
     * @param string $receiver Уникальный идентификатор пользователя Viber.
     * @param string $file Ссылка на файл.
     * @param array $params Дополнительные параметры.
     * @return array|null
     * @see sendMessage() Смотри тут
     * @api
     * @throws Exception
     */
    public function sendFile(string $receiver, string $file, array $params = [])
    {
        $this->request->post = [
            'receiver' => $receiver
        ];
        if (Text::isUrl($file)) {
            $this->request->post['type'] = 'file';
            $this->request->post['media'] = $file;
            $this->request->post['size'] = 10e4;
            $this->request->post['file_name'] = Text::resize($file, 150);
            if (!empty($params)) {
                $this->request->post = mmApp::arrayMerge($this->request->post, $params);
            }
            return $this->call('send_message');
        }
        return null;
    }

    /**
     * Запись логов.
     *
     * @param string $error Текст ошибки.
     * @throws Exception
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('viberApi.log', $error);
    }
}
