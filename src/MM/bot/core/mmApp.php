<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 10.03.2020
 * Time: 9:31
 */

namespace MM\bot\core;

defined('IS_SAVE_DB') or define('IS_SAVE_DB', false); // Если true, тогда данные сохраняются в бд. Иначе в файл.

defined('T_ALISA') or define('T_ALISA', 'alisa');           // Используется Алиса
defined('T_VK') or define('T_VK', 'vk');                    // Используется vk бот
defined('T_TELEGRAM') or define('T_TELEGRAM', 'telegram');  // Используется telegram бот
defined('T_VIBER') or define('T_VIBER', 'viber');           // Используется viber бот
defined('T_MARUSIA') or define('T_MARUSIA', 'marusia');     // Используется Маруся бот
defined('T_USER_APP') or define('T_USER_APP', 'user_application');  // Используется пользовательский тип приложения

defined('WELCOME_INTENT_NAME') or define('WELCOME_INTENT_NAME', 'welcome'); // Название интенат для приветствия
defined('HELP_INTENT_NAME') or define('HELP_INTENT_NAME', 'help');          // Название интента для помощи

/**
 * Class mmApp
 * @package bot\core
 */
class mmApp
{
    /**
     * @var string $appType :
     * Тип приложения. (Алиса, бот vk|telegram)
     */
    public static $appType;
    /**
     * @var array $config :
     * Основная конфигурация приложения.
     */
    public static $config = [
        /**
         * @var string: Директория, в которую будут записываться логи и ошибки выполнения
         */
        'error_log' => __DIR__ . '/../../logs',
        /**
         * @var string: Директория, в которую будут записываться json файлы
         */
        'json' => __DIR__ . '/../../json',
        /**
         * @var array: Настройка подключения к базе данных. Актуально если IS_SAVE_DB = true
         */
        'db' => [
            'host' => null, // Адрес расположения базы данных (localhost, https://example.com)
            'user' => null, // Имя пользователя
            'pass' => null, // Пароль пользователя
            'database' => null, // Название базы данных
        ],
        /**
         * @var bool: Использование локального хранилища вместо БД. Актуально для Алисы.
         * Важно! Чтобы опция работала, нужно поставить галку "Использовать хранилище данных в навыке" в кабинете разработчика.
         */
        'isLocalStorage' => false,
    ];
    /**
     * @var array $params :
     * Основные параметры приложения
     */
    public static $params = [
        /**
         * @var string|null: Viber токен для отправки сообщений, загрузки изображений и звуков
         */
        'viber_token' => null,
        /**
         * @var array|string|null: Имя пользователя, от которого будет отправляться сообщение
         */
        'viber_sender' => null,
        /**
         * @var string|null: Telegram токен для отправки сообщений, загрузки изображений и звуков
         */
        'telegram_token' => null,

        /**
         * @var string|null: Версия Vk api. По умолчанию используется v5.103
         */
        'vk_api_version' => null,

        /**
         * @var string|null: Код для проверки корректности Vk бота. Необходим для подтверждения бота.
         */
        'vk_confirmation_token' => null,

        /**
         * @var string|null: Vk Токен для отправки сообщений, загрузки изображений и звуков
         */
        'vk_token' => null,

        /**
         * @var string|null: Яндекс Токен для загрузки изображений и звуков в навыке
         */
        'yandex_token' => null,

        /**
         * @var bool: Актуально для Алисы!
         * Использовать в качестве идентификатора пользователя Id в поле session->user.
         * Если true, то для всех пользователей, которые авторизованы в Яндекс будет использоваться один токен, а не разный.
         */
        'y_isAuthUser' => false,

        /**
         * @var string|null: Идентификатор приложения.
         * Заполняется автоматически.
         */
        'app_id' => null,

        /**
         * @var string|null: Идентификатор пользователя.
         * Заполняется автоматически.
         */
        'user_id' => null,
        /**
         * @var string: Текст приветствия
         */
        'welcome_text' => 'Текст приветствия',
        /**
         * @var string: Текст помощи
         */
        'help_text' => 'Текст помощи',

        /**
         * @var array: Обрабатываемые команды.
         *  - @var string name: Название команды. Используется для идентификации команд
         *  - $var array slots: Какие слова активируют команду. (Можно использовать регулярные выражения если установлено свойство is_pattern)
         *  - $var bool is_pattern: Использовать регулярное выражение или нет. По умолчанию false
         *
         * Пример intent с регулярным выражением:
         * [
         *  'name' => 'regex',
         *  'slots' => [
         *      '\b{_value_}\b', // Поиск точного совпадения. Например, если _value_ = 'привет', поиск будет осуществляться по точному совпадению. Слово "приветствую" в данном случае не будет считаться как точка срабатывания
         *      '\b{_value_}[^\s]+\b', // Поиск по точному началу. При данной опции слово "приветствую" станет точкой срабатывания
         *      '(\b{_value_}(|[^\s]+)\b)', // Поиск по точному началу или точному совпадению. (Используется по умолчанию)
         *      '\b(\d{3})\b', // Поиск всех чисел от 100 до 999.
         *      '{_value_} \d {_value_}', // Поиск по определенному условию. Например регулярное "завтра в \d концерт", тогда точкой срабатывания станет пользовательский текст, в котором есть вхождение что и в регулярном выражении, где "\d" это любое число.
         *      '{_value_}', // Поиск любого похожего текста. Похоже на strpos()
         *      '...' // Поддерживаются любые регулярные выражения. Перед использованием стоит убедиться в их корректности на сайте: (https://regex101.com/)
         *  ],
         *  'is_pattern' => true
         * ]
         */
        'intents' => [
            [
                'name' => WELCOME_INTENT_NAME, // Название команды приветствия
                'slots' => [ // Слова, на которые будет срабатывать приветствие
                    'привет',
                    'здравст'
                ]
            ],
            [
                'name' => HELP_INTENT_NAME, // Название команды помощи
                'slots' => [ // Слова, на которые будет срабатывать помощь
                    'помощ',
                    'что ты умеешь'
                ]
            ],
        ]
    ];

    /**
     * Объединение 2 массивов
     *
     * @param array $array1 : Массив с котором необходимо объединить значение
     * @param array|null $array2 : Массив для объединения
     * @return array
     */
    public static function arrayMerge(array $array1, ?array $array2): array
    {
        $nArray = $array1;
        if ($array2) {
            array_walk($array2, function ($val, $key) use (&$nArray) {
                if (is_array($nArray[$key] ?? null)) {
                    $nArray[$key] = array_merge($nArray[$key], $val);
                } else {
                    $nArray[$key] = $val;
                }
            });
        }
        return $nArray;
    }

    /**
     * Инициализация конфигурации приложения
     *
     * @param array $config : Пользовательская конфигурация
     */
    public static function setConfig(array $config): void
    {
        static::$config = self::arrayMerge(static::$config, $config);
    }

    /**
     * Инициализация параметров приложения
     *
     * @param array $params : Пользовательские параметры
     */
    public static function setParams(array $params): void
    {
        static::$params = self::arrayMerge(static::$params, $params);
    }

    /**
     * Сохранение json файла
     *
     * @param string $fileName : Название файла
     * @param array|null $data : Сохраняемые данные
     * @return bool
     */
    public static function saveJson(string $fileName, ?array $data): bool
    {
        $path = static::$config['json'] ?? __DIR__ . '/../../json';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $fileName = str_replace('`', '', $fileName);
        $fJson = fopen("{$path}/{$fileName}", 'w');
        if ($fJson) {
            fwrite($fJson, json_encode($data, JSON_UNESCAPED_UNICODE));
            fclose($fJson);
            return true;
        } else {
            echo "mmApp::saveJson(): Не удалось создать/открыть файл: {$path}/{$fileName}\n";
            return false;
        }
    }

    /**
     * Сохранение логов
     *
     * @param string $fileName : Название файла
     * @param string $errorText : Текст ошибки
     * @return bool
     */
    public static function saveLog(string $fileName, string $errorText): bool
    {
        $path = static::$config['error_log'] ?? __DIR__ . '/../../logs';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $fError = fopen($path . '/' . $fileName, 'a');
        if ($fError) {
            $date = date('d-m-Y H:i:s');
            fwrite($fError, "[{$date}]: {$errorText}\n");
            fclose($fError);
            return true;
        } else {
            echo "mmApp::saveLog(): Не удалось создать/открыть файл: {$path}/{$fileName}\n";
            return false;
        }
    }
}
