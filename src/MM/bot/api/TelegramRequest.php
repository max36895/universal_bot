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
 * Отправка запросов к telegram серверу.
 *
 * Документация по telegram api.
 * @see (https://core.telegram.org/bots/api) Смотри тут
 *
 * Class TelegramRequest
 * @package bot\api
 */
class TelegramRequest
{
    /**
     * @const string: Адрес, на который отправляться запрос.
     */
    const API_ENDPOINT = 'https://api.telegram.org/bot';
    /**
     * Отправка запросов.
     * @var Request $request Отправка запросов.
     * @see Request Смотри тут
     */
    protected $request;
    /**
     * Строка с ошибками.
     * @var string|null $error Строка с ошибками.
     */
    protected $error;
    /**
     * Авторизационный токен бота, необходим для отправки данных.
     * @var string|null $token Авторизационный токен бота, необходим для отправки данных.
     */
    public $token;

    /**
     * TelegramRequest constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->request->maxTimeQuery = 5500;
        $this->token = null;
        if (isset(mmApp::$params['telegram_token'])) {
            $this->token = mmApp::$params['telegram_token'];
        }
    }

    /**
     * Установить токен.
     *
     * @param string|null $token Токен для загрузки данных на сервер.
     * @api
     */
    public function initToken($token): void
    {
        $this->token = $token;
    }

    /**
     * Получить url, на который будет отправляться запрос.
     *
     * @return string
     */
    protected function getUrl(): string
    {
        return self::API_ENDPOINT . mmApp::$params['telegram_token'] . '/';
    }

    /**
     * Отправка запросов к telegram серверу.
     *
     * @param string $method Отправляемый метод, что именно будет отправляться(Изображение, сообщение и тд).
     * @return array|null
     * @api
     */
    public function call(string $method): ?array
    {
        if ($this->token) {
            if ($method) {
                $data = $this->request->send($this->getUrl() . $method);
                if ($data['status']) {
                    if ($data['data']['ok'] == false) {
                        $this->error = $data['data']['description'];
                        $this->log('');
                        return null;
                    }
                    return $data['data'];
                }
                $this->log($data['err']);
            }
        } else {
            $this->log('Не указан telegram токен!');
        }
        return null;
    }

    /**
     * Отправка сообщения пользователю.
     *
     * @see https://core.telegram.org/bots/api#sendmessage Смотри тут
     * @param string|int $chatId Идентификатор пользователя/чата.
     * @param string $message Текст сообщения.
     * @param array $params Пользовательские параметры:
     * [
     *  - string|int chat_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string text: Текст отправляемого сообщения, 1-4096 символов после синтаксического анализа сущностей.
     *  - string parse_mode: Отправьте Markdown или HTML, если вы хотите, чтобы приложения Telegram отображали полужирный, курсивный, фиксированный по ширине текст или встроенные URL-адреса в сообщении вашего бота.
     *  - bool disable_web_page_preview: Отключает предварительный просмотр ссылок для ссылок в этом сообщении.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки сообщения
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *  ]
     * ]
     * or
     * [
     *  - 'ok' => false.
     *  - 'error_code' => int
     *  - 'description' => string
     * ]
     * @api
     */
    public function sendMessage($chatId, string $message, array $params = []): ?array
    {
        $method = 'sendMessage';
        $this->request->post = [
            'chat_id' => $chatId,
            'text' => $message
        ];
        if (count($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call($method);
    }

    /**
     * Отправка опроса пользователю.
     *
     * @param string|int $chatId Идентификатор пользователя/чата.
     * @param string $question Название опроса.
     * @param string|array $options Варианты ответов.
     * @param array $params Пользовательские параметры:
     * [
     *  - int|string chat_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string question: Опросный вопрос, 1-255 символов.
     *  - array|string options: A JSON-serialized list of answer options, 2-10 strings 1-100 characters each.
     *  - bool is_anonymous: True, если опрос должен быть анонимным, по умолчанию используется значение True.
     *  - string type: Типа опрос, “quiz” или “regular”, по умолчанию “regular”.
     *  - bool allows_multiple_answers: True, если опрос допускает несколько ответов, игнорируемых для опросов в режиме викторины, по умолчанию имеет значение False.
     *  - int correct_option_id: 0 - идентификатор правильного варианта ответа, необходимый для опросов в режиме викторины.
     *  - bool is_closed: Передайте True, если опрос Нужно немедленно закрыть. Это может быть полезно для предварительного просмотра опроса.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки опроса
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *      - 'poll' =>[
     *          - 'id' => int Уникальный идентификатор опроса
     *          - 'question' => string Вопрос
     *          - 'options' => [ Варианты ответов
     *              [
     *                  - 'text' => string Вариант ответа
     *                  - 'voter_count' => int Количество проголосовавших
     *              ]
     *          ]
     *          - 'total_voter_count' => int Общее количество пользователей проголосовавших в опросе
     *          - 'is_closed' => bool True, если опрос закрыт
     *          - 'is_anonymous' => bool True, если опрос анонимный
     *          - 'type' => string Тип опроса (regular, quiz)
     *          - 'allows_multiple_answers' => bool True, если в опросе допускается несколько ответов
     *          - 'correct_option_id' => int  0-основанный идентификатор правильного варианта ответа. Доступно только для опросов в режиме викторины, которые закрыты или были отправлены (не переадресованы) ботом или в приватный чат с ботом.
     *      ]
     *  ]
     * ]
     * @api
     */
    public function sendPoll($chatId, string $question, $options, array $params = []): ?array
    {
        $this->request->post = [
            'chat_id' => $chatId,
            'question' => $question,
        ];
        $isSend = true;
        if ($options) {
            if (is_array($options)) {
                $countOptions = count($options);
                if ($countOptions > 1) {
                    if ($countOptions > 10) {
                        $tmp = [];
                        $index = 0;
                        foreach ($options as $option) {
                            if ($index < 10) {
                                $tmp[] = $option;
                                $index++;
                            } else {
                                break;
                            }
                        }
                        $options = $tmp;
                    }
                    $options = json_encode($options);
                    $this->request->post['options'] = $options;
                } else {
                    $isSend = false;
                }
            }
        }
        if ($isSend) {
            if (count($params)) {
                $this->request->post = array_merge($params, $this->request->post);
            }
            return $this->call('sendPoll');
        } else {
            $this->log('Недостаточной количество вариантов. должно быть от 2 - 10 вариантов!');
            return null;
        }
    }

    /**
     * Отправка изображения пользователю.
     *
     * @param string|int $userId Идентификатор пользователя.
     * @param string $file Название файла.
     * @param string|null $desc Описание к фотографии.
     * @param array $params Пользовательские команды:
     * [
     *  - int|string chart_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string photo: Фото для отправки. Передайте file_id в качестве строки для отправки фотографии, которая существует на серверах Telegram (рекомендуется), передайте HTTP URL в качестве строки для Telegram, чтобы получить фотографию из интернета, или загрузите новую фотографию с помощью multipart/form-data. Более подробная информация об отправке файлов» (https://core.telegram.org/bots/api#sending-files).
     *  - string caption: Подпись к фотографии (также может использоваться при повторной отправке фотографий по file_id), 0-1024 символа после синтаксического анализа сущностей.
     *  - string parse_mode: Отправьте Markdown или HTML, если вы хотите, чтобы приложения Telegram отображали жирный, курсивный, фиксированный по ширине текст или встроенные URL-адреса в заголовке СМИ.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки изображения
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *      - 'photo" =>[
     *          [
     *              - 'file_id' => string Идентификатор изображения, который может быть использован для загрузки или повторного использования
     *              - 'file_unique_id' => string Уникальный идентификатор для этого изображения, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *              - 'file_size' => int Размер изображения
     *              - 'width' => int Ширина изображения
     *              - 'height' => int Высота изображения
     *          ]
     *      ]
     *  ]
     * ]
     * or
     * [
     *  - 'ok' => false.
     *  - 'error_code' => int
     *  - 'description' => string
     * ]
     * @api
     */
    public function sendPhoto($userId, string $file, $desc = null, array $params = []): ?array
    {
        $this->request->post = [
            'chat_id' => $userId
        ];
        if (is_file($file)) {
            $this->request->post['photo'] = curl_file_create($file);
        } else {
            $this->request->post['photo'] = $file;
        }
        if ($desc) {
            $this->request->post['caption'] = $desc;
        }
        if (count($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call('sendPhoto');
    }

    /**
     * Отправка документа пользователю.
     *
     * @param string|int $userId Идентификатор пользователя.
     * @param string $file Путь к файлу.
     * @param array $params Пользовательские параметры:
     * [
     *  - int|string chart_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string document: Файл для отправки. Передайте file_id в качестве строки для отправки файла, который существует на серверах Telegram (рекомендуется), передайте HTTP URL в качестве строки для Telegram, чтобы получить файл из интернета, или загрузите новый, используя multipart/form-data. Более подробная информация об отправке файлов» (https://core.telegram.org/bots/api#sending-files).
     *  - string thumb: Миниатюра отправленного файла; может быть проигнорирована, если генерация миниатюр для файла поддерживается на стороне сервера. Миниатюра должна быть в формате JPEG и иметь размер менее 200 кб. Ширина и высота миниатюры не должны превышать 320. Игнорируется, если файл не загружен с помощью multipart / form-data. Миниатюры не могут быть повторно использованы и могут быть загружены только в виде нового файла, поэтому вы можете передать “attach:/ / <file_attach_name>", если миниатюра была загружена с использованием составных / form-данных в разделе <file_attach_name>. Более подробная информация об отправке файлов » (https://core.telegram.org/bots/api#sending-files).
     *  - string caption: Заголовок документа (также может использоваться при повторной отправке документов по идентификатору file_id), 0-1024 символа после синтаксического анализа сущностей.
     *  - string parse_mode: Отправьте Markdown или HTML, если вы хотите, чтобы приложения Telegram отображали жирный, курсивный, фиксированный по ширине текст или встроенные URL-адреса в заголовке СМИ.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки документа
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *      - 'document" =>[
     *          - 'file_name' => string Оригинальное(исходное) имя файла
     *          - 'mime_type' => string MIME тип файла
     *          - 'thumb' => [
     *              - 'file_id' => string Идентификатор файла, который может быть использован для загрузки или повторного использования
     *              - 'file_unique_id' => string Уникальный идентификатор для этого файла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *              - 'file_size' => int Размер файла
     *              - 'width' => int Ширина изображения
     *              - 'height' => int Высота изображения
     *          ],
     *          - 'file_id' => string Идентификатор файла, который может быть использован для загрузки или повторного использования
     *          - 'file_unique_id' => string Уникальный идентификатор для этого файла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *          - 'file_size' => int Размер файла
     *      ]
     *  ]
     * ]
     * or
     * [
     *  - 'ok' => false.
     *  - 'error_code' => int
     *  - 'description' => string
     * ]
     * @api
     */
    public function sendDocument($userId, string $file, array $params = []): ?array
    {
        $this->request->post = [
            'chat_id' => $userId
        ];
        if (is_file($file)) {
            $this->request->post['document'] = curl_file_create($file);
        } else {
            $this->request->post['document'] = $file;
        }
        if (count($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call('sendDocument');
    }

    /**
     * Отправка Аудио файла пользователю.
     *
     * @param string|int $userId Идентификатор пользователя.
     * @param string $file Путь или содержимое файла.
     * @param array $params Пользовательские параметры:
     * [
     *  - int|string chart_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string audio: Аудио Файл для отправки. Передайте file_id в виде строки для отправки аудиофайла, существующего на серверах Telegram (рекомендуется), передайте HTTP URL в виде строки для Telegram, чтобы получить аудиофайл из интернета, или загрузите новый, используя multipart/form-data. Более подробная информация об отправке файлов» (https://core.telegram.org/bots/api#sending-files).
     *  - string thumb: Миниатюра отправленного файла; может быть проигнорирована, если генерация миниатюр для файла поддерживается на стороне сервера. Миниатюра должна быть в формате JPEG и иметь размер менее 200 кб. Ширина и высота миниатюры не должны превышать 320. Игнорируется, если файл не загружен с помощью multipart / form-data. Миниатюры не могут быть повторно использованы и могут быть загружены только в виде нового файла, поэтому вы можете передать “attach:/ / <file_attach_name>", если миниатюра была загружена с использованием составных / form-данных в разделе <file_attach_name>. Более подробная информация об отправке файлов» (https://core.telegram.org/bots/api#sending-files).
     *  - string caption: Подпись к фотографии (также может использоваться при повторной отправке фотографий по file_id), 0-1024 символа после синтаксического анализа сущностей.
     *  - string parse_mode: Отправьте Markdown или HTML, если вы хотите, чтобы приложения Telegram отображали жирный, курсивный, фиксированный по ширине текст или встроенные URL-адреса в заголовке СМИ.
     *  - int duration: Длительность звука в секундах.
     *  - string performer: Исполнитель.
     *  - string title: Название трека.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки аудио файла
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *      - 'audio" =>[
     *          - 'name' => string Оригинальное(исходное) название аудио файла
     *          - 'mime_type' => string MIME тип файла
     *          - 'duration' => int Длительность аудио файла
     *          - 'performer' => string Исполнитель аудио файла
     *          - 'thumb' => [ Для фотографий
     *              - 'file_id' => string Идентификатор файла, который может быть использован для загрузки или повторного использования
     *              - 'file_unique_id' => string Уникальный идентификатор для этого файла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *              - 'file_size' => int Размер файла
     *              - 'width' => int Ширина изображения
     *              - 'height' => int Высота изображения
     *          ],
     *          - 'file_id' => string Идентификатор аудио файла, который может быть использован для загрузки или повторного использования
     *          - 'file_unique_id' => string Уникальный идентификатор для этого аудиофайла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *          - 'file_size' => int Размер аудио файла
     *      ]
     *  ]
     * ]
     * or
     * [
     *  - 'ok' => false.
     *  - 'error_code' => int
     *  - 'description' => string
     * ]
     * @api
     */
    public function sendAudio($userId, string $file, array $params = []): ?array
    {
        $this->request->post = [
            'chat_id' => $userId
        ];
        if (is_file($file)) {
            $this->request->post['audio'] = curl_file_create($file);
        } else {
            $this->request->post['audio'] = $file;
        }
        if (count($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call('sendAudio');
    }

    /**
     * Отправка видео файла пользователю.
     *
     * @param string|int $userId Идентификатор пользователя.
     * @param string $file Путь к файлу.
     * @param array $params Пользовательские параметры:
     * [
     *  - int|string chart_id: Уникальный идентификатор целевого чата или имя пользователя целевого канала (в формате @channelusername).
     *  - string video: Видео для отправки. Передайте file_id в качестве строки для отправки видео, которое существует на серверах Telegram (рекомендуется), передайте HTTP URL в качестве строки для Telegram, чтобы получить видео из интернета, или загрузите новое видео с помощью multipart/form-data. Более подробная информация об отправке файлов» (https://core.telegram.org/bots/api#sending-files).
     *  - string thumb: Миниатюра отправленного файла; может быть проигнорирована, если генерация миниатюр для файла поддерживается на стороне сервера. Миниатюра должна быть в формате JPEG и иметь размер менее 200 кб. Ширина и высота миниатюры не должны превышать 320. Игнорируется, если файл не загружен с помощью multipart / form-data. Миниатюры не могут быть повторно использованы и могут быть загружены только в виде нового файла, поэтому вы можете передать “attach:/ / <file_attach_name>", если миниатюра была загружена с использованием составных / form-данных в разделе <file_attach_name>. Более подробная информация об отправке файлов » (https://core.telegram.org/bots/api#sending-files).
     *  - string caption: Заголовок видео (также может использоваться при повторной отправке видео по file_id), 0-1024 символа после разбора сущностей.
     *  - int duration: Длительность отправленного видео в секундах.
     *  - int width: Ширина видео.
     *  - int height: Высота видео.
     *  - bool supports_streaming: Передайте True, если загруженное видео подходит для потоковой передачи.
     *  - string parse_mode: Отправьте Markdown или HTML, если вы хотите, чтобы приложения Telegram отображали жирный, курсивный, фиксированный по ширине текст или встроенные URL-адреса в заголовке СМИ.
     *  - bool disable_notification: Отправляет сообщение молча. Пользователи получат уведомление без звука.
     *  - int reply_to_message_id: Если сообщение является ответом, то идентификатор исходного сообщения.
     *  - string reply_markup: Дополнительные опции интерфейса. JSON-сериализованный объект для встроенной клавиатуры, пользовательской клавиатуры ответа, инструкций по удалению клавиатуры ответа или принудительному получению ответа от пользователя.
     * ]
     * @return array|null
     * [
     *  - 'ok' => bool, Статус отправки видео файла
     *  - 'result' => [
     *      - 'message_id' => int Идентификатор сообщения
     *      - 'from' => [
     *          - 'id' => int Идентификатор отправителя
     *          - 'is_bot' => bool Тип отправителя (Бот или человек)
     *          - 'first_name' => string Имя отправителя
     *          - 'username' => string Никнейм отправителя
     *      ],
     *      - 'chat' => [
     *          - 'id' => int Идентификатор пользователя
     *          - 'first_name' => string Имя пользователя
     *          - 'last_name' => string Фамилия пользователя
     *          - 'username' => string Никнейм пользователя
     *          - 'type' => string Тип чата(Приватный и тд)
     *      ],
     *      - 'date' => int Дата отправки сообщения в unix time
     *      - 'text' => string Текст отправленного сообщения
     *      - 'video' =>[
     *          - 'name' => string Оригинальное(исходное) название аудио файла
     *          - 'mime_type' => string MIME тип файла
     *          - 'duration' => int Длительность аудио файла
     *          - 'thumb' => [ Для фотографий
     *              - 'file_id' => string Идентификатор файла, который может быть использован для загрузки или повторного использования
     *              - 'file_unique_id' => string Уникальный идентификатор для этого файла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *              - 'file_size' => int Размер файла
     *              - 'width' => int Ширина видео
     *              - 'height' => int Высота ширина
     *          ],
     *          - 'file_id' => string Идентификатор видео файла, который может быть использован для загрузки или повторного использования
     *          - 'file_unique_id' => string Уникальный идентификатор для этого видео файла, который должен быть одинаковым с течением времени и для разных ботов. Нельзя использовать для загрузки или повторного использования файла.
     *          - 'file_size' => int Размер аудио файла
     *          - 'width' => int Ширина видео
     *          - 'height' => int Высота ширина
     *      ]
     *  ]
     * ]
     * or
     * [
     *  - 'ok' => false.
     *  - 'error_code' => int
     *  - 'description' => string
     * ]
     * @api
     */
    public function sendVideo($userId, string $file, array $params = []): ?array
    {
        $this->request->post = [
            'chat_id' => $userId
        ];
        if (is_file($file)) {
            $this->request->post['video'] = curl_file_create($file);
        } else {
            $this->request->post['video'] = $file;
        }
        if (count($params)) {
            $this->request->post = array_merge($params, $this->request->post);
        }
        return $this->call('sendVideo');
    }

    /**
     * Сохранение логов в файл.
     *
     * @param string $error Текст ошибки.
     */
    protected function log(string $error): void
    {
        $error = sprintf("\n%sПроизошла ошибка при отправке запроса по адресу: %s\nОшибка:\n%s\n%s\n",
            date('(d-m-Y H:i:s)'), $this->request->url, $error, $this->error);
        mmApp::saveLog('telegramApi.log', $error);
    }
}
