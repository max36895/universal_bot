<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\core;


use MM\bot\controller\BotController;
use MM\bot\core\types\Alisa;
use MM\bot\core\types\Marusia;
use MM\bot\core\types\Telegram;
use MM\bot\core\types\TemplateTypeModel;
use MM\bot\core\types\Viber;
use MM\bot\core\types\Vk;
use MM\bot\models\UsersData;

/**
 * Class Bot
 * @package bot\core
 */
class Bot
{
    /**
     * Полученный запрос. В основном JSON.
     * @var bool|string|null $content Полученный запрос. В основном JSON.
     */
    private $content;
    /**
     * Логика приложения.
     * @var BotController|null $botController Логика приложения.
     * @see BotController Смотри тут
     */
    protected $botController;
    /**
     * Авторизационный токен если есть (Актуально для Алисы). Передастся в том случае, если пользователь произвел авторизацию в навыке.
     * @var string|null $auth Авторизационный токен если есть (Актуально для Алисы). Передастся в том случае, если пользователь произвел авторизацию в навыке.
     */
    private $auth;

    /**
     * Bot constructor.
     * @param string|null $type Тип приложения (alisa, vk, telegram).
     */
    public function __construct(?string $type = null)
    {
        $this->auth = null;
        $this->content = file_get_contents('php://input');

        if (!function_exists('getallheaders')) {
            function getallheaders(): array
            {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }

        $header = getallheaders();
        if (isset($header['Authorization'])) {
            $this->auth = str_replace('Bearer ', '', $header['Authorization']);
        }

        $this->botController = null;
        if ($type == null) {
            mmApp::$appType = T_ALISA;
        } else {
            mmApp::$appType = $type;
        }
    }

    /**
     * Инициализация типа бота через GET параметры.
     * Если присутствует get['type'], и он корректен(Равен одному из типов бота), тогда инициализация пройдет успешно.
     *
     * @return bool
     * @api
     */
    public function initTypeInGet(): bool
    {
        if (isset($_GET['type'])) {
            if ($_GET['type'] == T_TELEGRAM ||
                $_GET['type'] == T_ALISA ||
                $_GET['type'] == T_VIBER ||
                $_GET['type'] == T_VK ||
                $_GET['type'] == T_MARUSIA
            ) {
                mmApp::$appType = $_GET['type'];
                return true;
            }
        }
        return false;
    }

    /**
     * Инициализация конфигурации приложения.
     *
     * @param array|null $config Конфигурация приложения.
     * @api
     */
    public function initConfig(?array $config): void
    {
        if ($config) {
            mmApp::setConfig($config);
        }
    }

    /**
     * Инициализация параметров приложения.
     *
     * @param array|null $params Параметры приложения.
     * @api
     */
    public function initParams(?array $params): void
    {
        if ($params) {
            mmApp::setParams($params);
        }
    }

    /**
     * Подключение логики приложения.
     *
     * @param BotController $fn Контроллер с логикой приложения.
     * @api
     */
    public function initBotController(BotController $fn): void
    {
        $this->botController = $fn;
    }

    /**
     * Запуск приложения.
     *
     * @param TemplateTypeModel|null $userBotClass Пользовательский класс для обработки команд.
     * @return string
     * @api
     */
    public function run($userBotClass = null): string
    {
        $botClass = $type = null;
        switch (mmApp::$appType) {
            case T_ALISA:
                @header('Content-Type: application/json');
                $botClass = new Alisa();
                $type = UsersData::T_ALISA;
                break;

            case T_VK:
                $botClass = new Vk();
                $type = UsersData::T_VK;
                break;

            case T_TELEGRAM:
                $botClass = new Telegram();
                $type = UsersData::T_TELEGRAM;
                break;

            case T_VIBER:
                $botClass = new Viber();
                $type = UsersData::T_VIBER;
                break;

            case T_MARUSIA:
                @header('Content-Type: application/json');
                $botClass = new Marusia();
                $type = UsersData::T_MARUSIA;
                break;

            case T_USER_APP:
                if ($userBotClass) {
                    $botClass = $userBotClass;
                    $type = UsersData::T_USER_APP;
                }
        }

        if ($botClass) {
            if ($this->botController->userToken === null) {
                $this->botController->userToken = $this->auth;
            }
            if ($botClass->init($this->content, $this->botController)) {
                $userData = new UsersData();
                $userData->escapeString($this->botController->userId);
                if ($type) {
                    $userData->type = $type;
                }

                $isLocalStorage = (mmApp::$config['isLocalStorage'] && $botClass->isLocalStorage());

                if ($isLocalStorage) {
                    $botClass->isUsedLocalStorage = $isLocalStorage;
                    $this->botController->userData = $botClass->getLocalStorage();
                } else {
                    $sql = "`userId`=\"{$userData->escapeString($this->botController->userId)}\"";
                    if ($this->auth) {
                        $sql = "`userId`=\"{$userData->escapeString($this->botController->userToken)}\"";
                    }

                    $isNew = true;
                    if ($userData->whereOne($sql)) {
                        $this->botController->userData = $userData->data;
                        $isNew = false;
                    } else {
                        $this->botController->userData = null;
                        $userData->userId = $this->botController->userId;
                        $userData->meta = $this->botController->userMeta;
                    }
                }

                $this->botController->run();
                $content = $botClass->getContext();
                if (!$isLocalStorage) {
                    $userData->data = $this->botController->userData;

                    if ($isNew) {
                        $userData->save(true);
                    } else {
                        $userData->update();
                    }
                }

                if ($botClass->getError()) {
                    mmApp::saveLog('bot.log', $botClass->getError());
                }
                return $content;
            } else {
                mmApp::saveLog('bot.log', $botClass->getError());
            }
        } else {
            mmApp::saveLog('bot.log', 'Не удалось определить тип бота!');
        }
        @header('HTTP/1.0 404 Not Found');
        @header('Status: 404 Not Found');
        return 'notFound';
    }

    /**
     * Тестирование навыка.
     * Отображает только ответы навыка.
     * Никакой прочей информации (картинки, звуки, кнопки и тд) не отображаются!
     *
     * Для корректной работы, внутри логики навыка не должно быть пользовательских вызовов к серверу бота.
     *
     * @param bool $isShowResult Отображать полный навыка.
     * @param bool $isShowStorage Отображать данные из хранилища.
     * @param bool $isShowTime Отображать время выполнения запроса.
     * @api
     */
    public function test(bool $isShowResult = false, bool $isShowStorage = false, bool $isShowTime = true)
    {
        $count = 0;
        $state = [];
        do {
            if ($count == 0) {
                echo "Для выхода напишите exit\n";
                $query = 'Привет';
            } else {
                $query = trim(fgets(STDIN));
                if ($query == 'exit') {
                    break;
                }
            }
            if (!$this->content) {
                $this->content = json_encode($this->getSkillContent($query, $count, $state));
            }
            $timeStart = microtime(true);
            if (is_array($this->content)) {
                $this->content = json_encode($this->content);
            }

            $result = $this->run();
            $result = json_decode($result, true);
            if ($isShowResult) {
                printf("Результат работы: > \n%s\n\n", json_encode($result, JSON_UNESCAPED_UNICODE));
            }
            if ($isShowStorage) {
                printf("Данные в хранилище > \n%s\n\n", json_encode($this->botController->userData, JSON_UNESCAPED_UNICODE));
            }

            switch (mmApp::$appType) {
                case T_ALISA:
                    $result = $result['response']['text'];
                    break;
                default:
                    $result = $this->botController->text;
                    break;
            }
            printf("Бот: > %s\n", $result);
            if ($isShowTime) {
                $endTime = microtime(true) - $timeStart;
                echo "Время выполнения: {$endTime}\n";
            }
            if ($this->botController->isEnd) {
                break;
            }
            echo "Вы: > ";
            $this->content = null;
            $this->botController->text = $this->botController->tts = '';
            $state = $this->botController->userData;
            $count++;
        } while (1);
    }

    /**
     * Возвращает корректную конфигурацию для конкретного типа приложения.
     *
     * @param string $query Пользовательский запрос.
     * @param int $count Номер сообщения.
     * @param array|null $state Данные из хранилища.
     * @return array|mixed
     */
    protected function getSkillContent(string $query, int $count, ?array $state): array
    {
        /**
         * Все переменные используются внутри шаблонов
         */
        $content = [];
        $userId = 'user_local_test';
        switch (mmApp::$appType) {
            case T_ALISA:
                $content = include __DIR__ . '/skillsTemplateConfig/alisaConfig.php';
                break;

            case T_MARUSIA:
                $content = include __DIR__ . '/skillsTemplateConfig/marusiaConfig.php';
                break;

            case T_VK:
                $this->botController->isSend = false;
                $content = include __DIR__ . '/skillsTemplateConfig/vkConfig.php';
                break;

            case T_TELEGRAM:
                $this->botController->isSend = false;
                $content = include __DIR__ . '/skillsTemplateConfig/telegramConfig.php';
                break;

            case T_VIBER:
                $this->botController->isSend = false;
                $content = include __DIR__ . '/skillsTemplateConfig/viberConfig.php';
                break;
        }
        return $content;
    }
}
