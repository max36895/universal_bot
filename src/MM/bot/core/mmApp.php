<?php

namespace MM\bot\core;

use Exception;
use MM\bot\models\db\DbControllerModel;

defined('T_ALISA') or define('T_ALISA', 'alisa');           // Используется Алиса
defined('T_VK') or define('T_VK', 'vk');                    // Используется vk бот
defined('T_TELEGRAM') or define('T_TELEGRAM', 'telegram');  // Используется telegram бот
defined('T_VIBER') or define('T_VIBER', 'viber');           // Используется viber бот
defined('T_MARUSIA') or define('T_MARUSIA', 'marusia');     // Используется Маруся
defined('T_SMARTAPP') or define('T_SMARTAPP', 'smart-app');     // Используется Сбер SmartApp
defined('T_USER_APP') or define('T_USER_APP', 'user_application');  // Используется пользовательский тип приложения

defined('WELCOME_INTENT_NAME') or define('WELCOME_INTENT_NAME', 'welcome'); // Название интенат для приветствия
defined('HELP_INTENT_NAME') or define('HELP_INTENT_NAME', 'help');          // Название интента для помощи

/**
 * Статический класс, хранящий состояние и параметры приложения.
 *
 * Class mmApp
 * @package bot\core
 */
class mmApp
{
    /**
     * Использование стороннего контроллера для подключения к БД.
     * Класс должен быть унаследован от DbControllerModel. Стоит применять в том случае, если используется другая СУБД.
     * Если опция не передается, то используется стандартное подключение MySql.
     * @var DbControllerModel|null
     * @see DbControllerModel
     */
    public static $userDbController = null;
    /**
     * Куда сохраняются пользовательские данные. Если false, то данные сохраняются в файл, иначе в бд. По умолчанию false.
     * @var {boolean} $isSaveDb
     */
    public static $isSaveDb = false;
    /**
     * Тип приложения. (Алиса, бот vk|telegram).
     * @var string $appType Тип приложения. (Алиса, бот vk|telegram).
     */
    public static $appType;
    /**
     * Основная конфигурация приложения.
     * @var array $config Основная конфигурация приложения.
     * [
     *  - string error_log Директория, в которую будут записываться логи и ошибки выполнения.
     *  - string json Директория, в которую будут записываться json файлы.
     *  - array db Настройка подключения к базе данных. Актуально если mmApp::$isSaveDb = true.
     *  - bool isLocalStorage Использование локального хранилища вместо БД. Актуально для Алисы.
     *      - Важно! Чтобы опция работала, нужно поставить галку "Использовать хранилище данных в навыке" в кабинете разработчика.
     * ]
     */
    public static $config = [
        'error_log' => __DIR__ . '/../../logs',
        'json' => __DIR__ . '/../../json',
        'db' => [
            'host' => null, // Адрес расположения базы данных (localhost, https://example.com)
            'user' => null, // Имя пользователя
            'pass' => null, // Пароль пользователя
            'database' => null, // Название базы данных
        ],
        'isLocalStorage' => false,
    ];
    /**
     * Основные параметры приложения.
     * @var array $params Основные параметры приложения.
     * [
     * - string|null viber_token Viber токен для отправки сообщений, загрузки изображений и звуков.
     * - string|null viber_sender Имя пользователя, от которого будет отправляться сообщение.
     * - int|null viber_api_version Версия api для viber.
     * - string|null telegram_token Telegram токен для отправки сообщений, загрузки изображений и звуков.
     * - string|null vk_api_version Версия Vk api. По умолчанию используется v5.103.
     * - string|null vk_confirmation_token Код для проверки корректности Vk бота. Необходим для подтверждения бота.
     * - string|null vk_token Vk Токен для отправки сообщений, загрузки изображений и звуков.
     * - string|null yandex_token Яндекс Токен для загрузки изображений и звуков в навыке.
     * - string|null yandex_speech_kit_token Токен для отправки запросов в Yandex speesh kit.
     * - bool y_isAuthUser Актуально для Алисы!
     *      - Использовать в качестве идентификатора пользователя Id в поле session->user.
     *      - Если true, то для всех пользователей, которые авторизованы в Яндекс будет использоваться один токен, а не разный.
     * - string|null app_id Идентификатор приложения.
     *      - Заполняется автоматически.
     * - string|null user_idИдентификатор пользователя.
     *      - Заполняется автоматически.
     * - string|string[]|null welcome_text Текст, или масив из текста для приветствия.
     * - string|string[]|null help_text Текст, или масив из текста для помощи.
     * - array intents Обрабатываемые команды.
     *      - name: Название команды. Используется для идентификации команд.
     *      - slots: Какие слова активируют команду. (Можно использовать регулярные выражения если установлено свойство is_pattern).
     *      - is_pattern: Использовать регулярное выражение или нет. По умолчанию false.
     *
     *  Пример intent с регулярным выражением:
     *  [
     *      -'name' => 'regex',
     *      -'slots' => [
     *          -'\b{_value_}\b', // Поиск точного совпадения. Например, если _value_ = 'привет', поиск будет осуществляться по точному совпадению. Слово "приветствую" в данном случае не будет считаться как точка срабатывания
     *          -'\b{_value_}[^\s]+\b', // Поиск по точному началу. При данной опции слово "приветствую" станет точкой срабатывания
     *          -'(\b{_value_}(|[^\s]+)\b)', // Поиск по точному началу или точному совпадению.
     *          -'\b(\d{3})\b', // Поиск всех чисел от 100 до 999.
     *          -'{_value_} \d {_value_}', // Поиск по определенному условию. Например регулярное "завтра в \d концерт", тогда точкой срабатывания станет пользовательский текст, в котором есть вхождение что и в регулярном выражении, где "\d" это любое число.
     *          -'{_value_}', // Поиск любого похожего текста. Похоже на strpos()
     *          -'...' // Поддерживаются любые регулярные выражения. Перед использованием стоит убедиться в их корректности на сайте: (https://regex101.com/)
     *      ],
     *      -'is_pattern' => true
     *  ]
     *  - string|null utm_text Текст для UTM метки. По умолчанию utm_source=Yandex_Alisa&utm_medium=cpc&utm_campaign=phone
     * ]
     */
    public static $params = [
        'viber_token' => null,
        'viber_sender' => null,
        'viber_api_version' => null,
        'telegram_token' => null,
        'vk_api_version' => null,
        'vk_confirmation_token' => null,
        'vk_token' => null,
        'yandex_token' => null,
        'yandex_speech_kit_token' => null,
        'y_isAuthUser' => false,
        'app_id' => null,
        'user_id' => null,
        'welcome_text' => 'Текст приветствия',
        'help_text' => 'Текст помощи',
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
        ],
        'utm_text' => null
    ];

    /**
     * Объединение 2 массивов.
     *
     * @param array $array1 Массив с котором необходимо объединить значение.
     * @param array|null $array2 Массив для объединения.
     * @return array
     * @api
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
     * Инициализация конфигурации приложения.
     *
     * @param array $config Пользовательская конфигурация.
     * @api
     */
    public static function setConfig(array $config): void
    {
        static::$config = self::arrayMerge(static::$config, $config);
    }

    /**
     * Инициализация параметров приложения.
     *
     * @param array $params Пользовательские параметры.
     * @api
     */
    public static function setParams(array $params): void
    {
        static::$params = self::arrayMerge(static::$params, $params);
    }

    /**
     * Переопределения места, для хранения данных пользователя.
     *
     * @param bool $isSaveDb Если true, то данные сохраняются в БД, иначе в файл.
     */
    public static function setIsSaveDb($isSaveDb = false): void
    {
        static::$isSaveDb = $isSaveDb;
    }

    /**
     * Сохранение данных в json файл.
     *
     * @param string $fileName Название файла.
     * @param array|null $data Сохраняемые данные.
     * @return bool
     * @throws Exception
     * @api
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
            $error = "mmApp::saveJson(): Не удалось создать/открыть файл: {$path}/{$fileName}\n";
            echo $error;
            throw new Exception($error);
        }
    }

    /**
     * Сохранение логов.
     *
     * @param string $fileName Название файла.
     * @param string $errorText Текст ошибки.
     * @return bool
     * @throws Exception
     * @api
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
            $error = "mmApp::saveJson(): Не удалось создать/открыть файл: {$path}/{$fileName}\n";
            echo $error;
            throw new Exception($error);
        }
    }
}
