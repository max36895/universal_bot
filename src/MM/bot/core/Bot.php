<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 07.03.2020
 * Time: 16:47
 */

namespace MM\bot\core;


use MM\bot\controller\BotController;
use MM\bot\core\types\Alisa;
use MM\bot\core\types\Telegram;
use MM\bot\core\types\TemplateTypeModel;
use MM\bot\core\types\Viber;
use MM\bot\core\types\Vk;
use MM\bot\models\UsersData;

/**
 * Class Bot
 * @package bot\core
 *
 * @property string|array $content: Тело запроса
 * @see BotController
 * @property BotController $botController: Логика приложения
 * @property string $auth: Авторизационный токен если есть (Актуально для Алисы). Передастся в том случае, если пользователь произвел авторизацию в навыке.
 */
class Bot
{
    /**
     * @var bool|string Полученный запрос. В основном JSON
     */
    private $content;

    protected $botController;
    private $auth;

    /**
     * Bot constructor.
     * @param string|null $type : Тип приложения (alisa, vk, telegram)
     */
    public function __construct($type = null)
    {
        $this->auth = null;
        $this->content = file_get_contents('php://input');

        if (function_exists('getallheaders')) {
            $header = getallheaders();
        } else {
            $header = [];
        }
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
     * Если присутствует get['type'], и он корректен(Равен одному из типов бота), тогда инициализация пройдет успешно
     *
     * @return bool
     */
    public function initTypeInGet(): bool
    {
        if (isset($_GET['type'])) {
            if ($_GET['type'] == T_TELEGRAM ||
                $_GET['type'] == T_ALISA ||
                $_GET['type'] == T_VIBER ||
                $_GET['type'] == T_VK) {
                mmApp::$appType = $_GET['type'];
                return true;
            }
        }
        return false;
    }

    /**
     * Инициализация конфигурации приложения.
     *
     * @param array|null $config : Конфигурация приложения
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
     * @param array|null $params : Параметры приложения
     */
    public function initParams(?array $params): void
    {
        if ($params) {
            mmApp::setParams($params);
        }
    }

    /**
     * Подключение логики приложения
     *
     * @param BotController $fn : Контроллер с логикой приложения
     */
    public function initBotController(BotController $fn): void
    {
        $this->botController = $fn;
    }

    /**
     * Запуск приложения
     *
     * @param TemplateTypeModel|null $userBotClass : Пользовательский класс для обработки команд
     * @return string
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

                $sql = "`user_id`=\"{$userData->escapeString($this->botController->userId)}\"";
                if ($this->auth) {
                    $sql = "`user_id`=\"{$userData->escapeString($this->botController->userToken)}\"";
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

                $this->botController->run();
                $content = $botClass->getContext();
                $userData->data = $this->botController->userData;

                if ($isNew) {
                    $userData->save(true);
                } else {
                    $userData->update();
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
     */
    public function test()
    {
        $count = 0;
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
                $content = [];
                $userId = 'user_local_test';
                switch (mmApp::$appType) {
                    case T_ALISA:
                        $content = [
                            'meta' => [
                                'location' => 'ru-Ru',
                                'timezone' => 'UTC',
                                'client_id' => 'local',
                                'interfaces' => [
                                    'payments' => null,
                                    'account_linking' => null
                                ]
                            ],
                            'session' => [
                                'message_id' => $count,
                                'session_id' => 'local',
                                'skill_id' => 'local_test',
                                'user_id' => $userId,
                                'new' => ($count == 0)
                            ],
                            'request' => [
                                'command' => strtolower($query),
                                'original_utterance' => $query,
                                'nlu' => [],
                                'type' => 'SimpleUtterance'
                            ],
                            'version' => '1.0'
                        ];
                        break;
                    case T_VK:
                        $this->botController->isSend = false;
                        $content = [
                            'type' => 'message_new',
                            'object' => [
                                'message' => [
                                    'from_id' => $userId,
                                    'text' => $query,
                                    'id' => $count
                                ]
                            ]
                        ];
                        break;
                    case T_TELEGRAM:
                        $content = [
                            'message' => [
                                'chat' => [
                                    'id' => $userId,
                                ],
                                'message' => [
                                    'text' => $query,
                                    'message_id' => $count
                                ]
                            ]
                        ];
                        break;
                    case T_VIBER:
                        $content = [
                            'event' => 'message',
                            'message' => [
                                'text' => $query,
                                'type' => 'text'
                            ],
                            'message_token' => time(),
                            'sender' => [
                                'id' => $userId,
                                'name' => 'local_name',
                                'api_version' => 8
                            ]
                        ];
                        break;
                }
                $this->content = json_encode($content);
            }
            $timeStart = microtime(true);
            if (is_array($this->content)) {
                $this->content = json_encode($this->content);
            }
            $result = json_decode($this->run(), true);
            switch (mmApp::$appType) {
                case T_ALISA:
                    $result = $result['response']['text'];
                    break;
                default:
                    $result = $this->botController->text;
                    break;
            }
            printf("Бот: > %s\n", $result);
            $endTime = microtime(true) - $timeStart;
            echo "time: {$endTime}\n";
            if ($this->botController->isEnd) {
                break;
            }
            echo "Вы: > ";
            $this->content = null;
            $this->botController->text = $this->botController->tts = '';
            $count++;
        } while (1);
    }
}
