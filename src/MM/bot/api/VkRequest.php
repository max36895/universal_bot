<?php

namespace MM\bot\api;


use Exception;
use MM\bot\api\request\Request;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отправку запросов на Vk сервер.
 *
 * Документация по ВК api.
 * @see (https://vk.ru/dev/bots_docs) Смотри тут
 *
 * Class VkRequest
 * @package bot\api
 */
class VkRequest
{
    /**
     * @const string Стандартная версия Api.
     */
    const VK_API_VERSION = '5.103';
    /**
     * @const string Адрес, на который будут отправляться запросы.
     */
    const VK_API_ENDPOINT = 'https://api.vk.ru/method/';

    /**
     * Используемая версия Api.
     * @var string $vkApiVersion
     */
    protected $vkApiVersion;
    /**
     * Отправка запросов.
     * @var Request $request
     * @see Request Смотри тут
     */
    protected $request;
    /**
     * Текст ошибки.
     * @var string|null $error
     */
    protected $error;

    /**
     * Vk токен, необходимый для отправки запросов на сервер.
     * @var string|null $token
     */
    public $token;
    /**
     * Тип контента файла.
     * True если передается содержимое файла. По умолчанию: false.
     * @var bool $isAttachContent
     */
    public $isAttachContent;

    /**
     * VkRequest constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->request->maxTimeQuery = 5500;
        $this->isAttachContent = false;
        if (isset(mmApp::$params['vk_api_version']) && mmApp::$params['vk_api_version']) {
            $this->vkApiVersion = mmApp::$params['vk_api_version'];
        } else {
            $this->vkApiVersion = self::VK_API_VERSION;
        }
        $this->token = null;
        if (isset(mmApp::$params['vk_token'])) {
            $this->initToken(mmApp::$params['vk_token']);
        }
    }

    /**
     * Установить vk токен.
     *
     * @param string $token Токен для загрузки данных на сервер.
     * @api
     */
    public function initToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Вызов методов vk.
     *
     * @param string $method Название метода.
     * @return array|null
     * @api
     * @throws Exception
     */
    public function call(string $method): ?array
    {
        if ($this->token) {
            $this->request->header = null;
            $this->request->post['access_token'] = $this->token;
            $this->request->post['v'] = $this->vkApiVersion;
            $data = $this->request->send(self::VK_API_ENDPOINT . $method);
            if ($data['status']) {
                $this->error = json_encode($data['err'] ?? [], JSON_UNESCAPED_UNICODE);
                if (isset($data['data']['error'])) {
                    $this->error = json_encode($data['data']['error']);
                    $this->log('');
                    return null;
                }
                return $data['data']['response'] ?? $data['data'];
            }
            $this->log($data['err']);
        } else {
            $this->log('Не указан vk токен!');
        }
        return null;
    }

    /**
     * Загрузка файлов на vk сервер.
     *
     * @param string $url Адрес, на который отправляется запрос.
     * @param string $file Загружаемый файл(ссылка или содержимое файла).
     * @return array|null
     * [
     *  - 'photo' => array
     *  - 'server' => string
     *  - 'hash' => string
     * ]
     * or
     * [
     *  - 'file' => array
     * ]
     * @api
     * @throws Exception
     */
    public function upload(string $url, string $file): ?array
    {
        $this->request->attach = $file;
        $this->request->isAttachContent = $this->isAttachContent;
        $this->request->header = [Request::HEADER_FORM_DATA];
        $data = $this->request->send($url);
        if ($data['status']) {
            if (isset($data['data']['error'])) {
                $this->error = json_encode($data['data']['error'], JSON_UNESCAPED_UNICODE);
                $this->log('');
                return null;
            }
            return $data['data'];
        }
        $this->log($data['err']);
        return null;
    }

    /**
     * Отправка сообщения пользователю.
     *
     * @param string|int $peerId Идентификатор места назначения.
     * @param string $message Текст сообщения.
     * @param array $params Пользовательские параметры:
     * [
     * - integer user_id: User ID (by default — current user).
     * - integer random_id: Unique identifier to avoid resending the message.
     * - integer peer_id: Destination ID. "For user: 'User ID', e.g. '12345'. For chat: '2000000000' + 'chat_id', e.g. '2000000001'. For community: '- community ID', e.g. '-12345'. ".
     * - string domain: User's short address (for example, 'illarionov').
     * - integer chat_id: ID of conversation the message will relate to.
     * - array[integer] user_ids: IDs of message recipients (if new conversation shall be started).
     * - string message: (Required if 'attachments' is not set.) Text of the message.
     * - number lat: Geographical latitude of a check-in, in degrees (from -90 to 90).
     * - number long: Geographical longitude of a check-in, in degrees (from -180 to 180).
     * - string attachment: (Required if 'message' is not set.) List of objects attached to the message, separated by commas, in the following format: "<owner_id>_<media_id>", '' — Type of media attachment: 'photo' — photo, 'video' — video, 'audio' — audio, 'doc' — document, 'wall' — wall post, '<owner_id>' — ID of the media attachment owner. '<media_id>' — media attachment ID. Example: "photo100172_166443618".
     * - integer reply_to.
     * - array[integer] forward_messages: ID of forwarded messages, separated with a comma. Listed messages of the sender will be shown in the message body at the recipient's. Example: "123,431,544".
     * - string forward.
     * - integer sticker_id: Sticker id.
     * - integer group_id: Group ID (for group messages with group access token).
     * - string keyboard.
     * - string payload.
     * - boolean dont_parse_links.
     * - boolean disable_mentions.
     * ]
     * @return array|int|null
     * - int: response
     * or in user_ids
     * [[
     *  - 'peer_id' => int Идентификатор назначения
     *  - 'message_id' => int Идентификатор сообщения
     *  - 'error' => array
     * ]]
     * @api
     * @throws Exception
     */
    public function messagesSend($peerId, string $message, array $params = [])
    {
        $method = 'messages.send';
        $this->request->post = [
            'peer_id' => $peerId,
            'message' => $message
        ];

        if (!is_numeric($peerId)) {
            $this->request->post['domain'] = $peerId;
            unset($this->request->post['peer_id']);
        }

        if (isset($params['random_id'])) {
            $this->request->post['random_id'] = $params['random_id'];
        } else {
            $this->request->post['random_id'] = microtime(false);
        }

        if (isset($params['attachments'])) {
            $this->request->post['attachment'] = implode(',', $params['attachments']);
            unset($params['attachments']);
        }

        if (isset($params['template'])) {
            if (is_array($params['template'])) {
                $params['template'] = json_encode($params['template']);
            }
            $this->request->post['template'] = $params['template'];
            unset($params['template']);
        }

        if (isset($params['keyboard'])) {
            if (isset($this->request->post['template'])) {
                $this->call($method);
                unset($this->request->post['template']);
            }
            if (is_array($params['keyboard'])) {
                $params['template'] = json_encode($params['keyboard']);
            }
            $this->request->post['keyboard'] = $params['keyboard'];
            unset($params['keyboard']);
        }

        if (!empty($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call($method);
    }

    /**
     * Получение данные о пользователе.
     *
     * @param array|string|int $userId Идентификатор пользователя.
     * @param array $params Пользовательские параметры:
     * [
     * - array[string] user_ids: User IDs or screen names ('screen_name'). By default, current user ID.
     * - array fields: Profile fields to return. Sample values: 'nickname', 'screen_name', 'sex', 'bdate' (birthdate), 'city', 'country', 'timezone', 'photo', 'photo_medium', 'photo_big', 'has_mobile', 'contacts', 'education', 'online', 'counters', 'relation', 'last_seen', 'activity', 'can_write_private_message', 'can_see_all_posts', 'can_post', 'universities'.
     * - string name_case: Case for declension of user name and surname: 'nom' — nominative (default), 'gen' — genitive , 'dat' — dative, 'acc' — accusative , 'ins' — instrumental , 'abl' — prepositional.
     * ]
     * @return array|null
     * [
     *  - 'id' => int Идентификатор пользователя
     *  - 'first_name' => string Имя пользователя
     *  - 'last_name' => string Фамилия пользователя
     *  - 'deactivated' => string Возвращается, если страница удалена или заблокирована
     *  - 'is_closed' => bool Скрыт ли профиль настройками приватности
     *  - 'can_access_closed' => bool Может ли текущий пользователь видеть профиль при is_closed = 1 (например, он есть в друзьях).
     * ]
     * @api
     * @throws Exception
     */
    public function usersGet($userId, array $params = []): ?array
    {
        if (is_array($userId)) {
            $this->request->post = ['user_ids' => $userId];
        } else {
            $this->request->post = ['user_id' => $userId];
        }
        $this->request->post = array_merge($this->request->post, $params);
        return $this->call('users.get');
    }

    /**
     * Получение данные по загрузке изображения на vk сервер.
     *
     * @param string|int $peerId Идентификатор места назначения.
     * @return array|null
     * [
     *  - 'upload_url' => string Адрес сервера для загрузки изображения
     *  - 'album_id' => int Идентификатор альбома
     *  - 'group_id' => int Идентификатор сообщества
     * ]
     * @api
     * @throws Exception
     */
    public function photosGetMessagesUploadServer($peerId): ?array
    {
        $this->request->post = ['peer_id' => $peerId];
        return $this->call('photos.getMessagesUploadServer');
    }

    /**
     * Сохранение файла на vk сервер.
     *
     * @param string $photo Фотография.
     * @param string $server Сервер.
     * @param string $hash Хэш.
     * @return array|null
     * [
     *  - 'id' => int Идентификатор изображения
     *  - 'pid' => int
     *  - 'aid' => int
     *  - 'owner_id' => int Идентификатор пользователя, загрузившего изображение
     *  - 'src' => string Расположение изображения
     *  - 'src_big' => string Расположение большой версии изображения
     *  - 'src_small' => string Расположение маленькой версии изображения
     *  - 'created' => int Дата загрузки изображения в unix time
     *  - 'src_xbig' => string Для изображений с большим разрешением
     *  - 'src_xxbig' => string Для изображений с большим разрешением
     * ]
     * @see upload() Смотри тут
     * @api
     * @throws Exception
     */
    public function photosSaveMessagesPhoto(string $photo, string $server, string $hash): ?array
    {
        $this->request->post = [
            'photo' => $photo,
            'server' => $server,
            'hash' => $hash
        ];
        return $this->call('photos.saveMessagesPhoto');
    }

    /**
     * Получение данные по загрузке файла на vk сервер.
     *
     * @param string|int $peerId Идентификатор места назначения.
     * @param string $type ('doc' - Обычный документ, 'audio_message' - Голосовое сообщение, 'graffiti' - Граффити).
     * @return array|null
     * [
     *  - 'upload_url' => url Адрес сервера для загрузки документа
     * ]
     * @api
     * @throws Exception
     */
    public function docsGetMessagesUploadServer($peerId, string $type): ?array
    {
        $this->request->post = [
            'peer_id' => $peerId,
            'type' => $type
        ];
        return $this->call('docs.getMessagesUploadServe');
    }

    /**
     * Загрузка файла на vk сервер.
     *
     * @param string $file Сам файл.
     * @param string $title Заголовок файла.
     * @param string|null $tags Теги, по которым будет осуществляться поиск.
     * @return array|null
     * [
     *  - 'type' => string Тип загруженного документа
     *  - 'graffiti' => [
     *      - 'id' => int Идентификатор документа
     *      - 'owner_id' => int Идентификатор пользователя, загрузившего документ
     *      - 'url' => string Адрес документа, по которому его можно загрузить
     *      - 'width' => int Ширина изображения в px
     *      - 'height' => int Высота изображения в px
     *  ]
     * or
     *  - 'audio_message' => [
     *      - 'id' => int Идентификатор документа
     *      - 'owner_id' => int Идентификатор пользователя, загрузившего документ
     *      - 'duration' => int Длительность аудио сообщения в секундах
     *      - 'waveform' => int[] Массив значений для визуального отображения звука
     *      - 'link_ogg' => url .ogg файла
     *      - 'link_mp3' => url .mp3 файла
     *  ]
     * or
     *  - 'doc' =>[
     *      - 'id' => int Идентификатор документа
     *      - 'owner_id' => int Идентификатор пользователя, загрузившего документ
     *      - 'url' => string Адрес документа, по которому его можно загрузить
     *      - 'title' => string Название документа
     *      - 'size' => int Размер документа в байтах
     *      - 'ext' => string Расширение документа
     *      - 'date' => int Дата добавления в формате unix time
     *      - 'type' => int Тип документа. (1 - текстовый документ; 2 - архивы; 3 - gif; 4 - изображения; 5 - аудио; 6 - видео; 7 - электронные книги; 8 - неизвестно)
     *      - 'preview' => [ Информация для предварительного просмотра документа.
     *          - 'photo' => [Изображения для предпросмотра.
     *              - 'sizes' => array Массив копий изображения в разных размерах. Подробное описание структуры (https://vk.ru/dev/objects/photo_sizes)
     *          ]
     *          or
     *          - 'graffiti' => [ Данные о граффити
     *              - 'src' => string url Документа с граффити
     *              - 'width' => int Ширина изображения в px
     *              - 'height' => int Высота изображения в px
     *          ]
     *          or
     *          - 'audio_message' => [ Данные об аудиосообщении
     *              - 'duration' => int Длительность аудио сообщения в секундах
     *              - 'waveform' => int[] Массив значений для визуального отображения звука
     *              - 'link_ogg' => url .ogg файла
     *              - 'link_mp3' => url .mp3 файла
     *          ]
     *      ]
     *  ]
     *  - 'id' => int Идентификатор документа
     *  - 'owner_id' => int Идентификатор пользователя, загрузившего документ
     *  - 'url' => string Адрес документа, по которому его можно загрузить (Для граффити и документа)
     *  - 'width' => int Ширина изображения в px (Для граффити)
     *  - 'height' => int Высота изображения в px (Для граффити)
     *  - 'duration' => int Длительность аудио сообщения в секундах(Для Голосового сообщения)
     *  - 'waleform' => int[] Массив значений для визуального отображения звука(Для Голосового сообщения)
     *  - 'link_ogg' => url .ogg файла(Для Голосового сообщения)
     *  - 'link_mp3' => url .mp3 файла(Для Голосового сообщения)
     * ]
     * @api
     * @throws Exception
     */
    public function docsSave(string $file, string $title, ?string $tags = null): ?array
    {
        $this->request->post = [
            'file' => $file,
            'title' => $title
        ];
        if ($tags) {
            $this->request->post['tags'] = $tags;
        }
        return $this->call('docs.save');
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
        mmApp::saveLog('vkApi.log', $error);
    }
}
