<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\core\types;


use MM\bot\components\button\Buttons;
use MM\bot\components\sound\types\AlisaSound;
use MM\bot\components\standard\Text;
use MM\bot\controller\BotController;
use MM\bot\core\mmApp;

/**
 * Класс, отвечающий за корректную инициализацию и отправку ответа для Алисы
 * Class Alisa
 * @package bot\core\types
 * @see TemplateTypeModel Смотри тут
 */
class Alisa extends TemplateTypeModel
{
    /**
     * @const string Версия Алисы.
     */
    private const VERSION = '1.0';
    /**
     * @const float Максимально время, за которое должен ответить навык.
     */
    private const MAX_TIME_REQUEST = 2.8;
    /**
     * Информация о сессии пользователя.
     * @var array|null $session Информация о сессии пользователя.
     */
    protected $session;
    /**
     * Использование хранилища.
     * @var bool $isState Использование хранилища.
     */
    protected $isState = false;
    /**
     * Название хранилища. Зависит от куда берутся данные(локально, глобально).
     * @var string|null $stateName Название хранилища. Зависит от куда берутся данные(локально, глобально).
     */
    protected $stateName;

    /**
     * Получение ответа пользователю.
     *
     * @return array
     */
    protected function getResponse(): array
    {
        $response = [];
        $response['text'] = Text::resize(AlisaSound::removeSound($this->controller->text), 1024);
        $response['tts'] = Text::resize($this->controller->tts, 1024);

        if ($this->controller->isScreen) {
            if (count($this->controller->card->images)) {
                $response['card'] = $this->controller->card->getCards();
            }
            $response['buttons'] = $this->controller->buttons->getButtons(Buttons::T_ALISA_BUTTONS);
        }
        $response['end_session'] = $this->controller->isEnd;
        return $response;
    }

    /**
     * Инициализация параметров.
     *
     * @param string|null $content Запрос пользователя.
     * @param BotController $controller Ссылка на класс с логикой навык/бота.
     * @return bool
     * @see TemplateTypeModel::init() Смотри тут
     * @api
     */
    public function init(?string $content, BotController &$controller): bool
    {
        if ($content) {
            $content = json_decode($content, true);
            if (!isset($content['session'], $content['request'])) {
                if (isset($content['account_linking_complete_event'])) {
                    $this->controller->isAuthSuccess = true;
                    return true;
                }
                $this->error = 'Alisa::init(): Не корректные данные!';
                return false;
            }

            $this->controller = &$controller;
            $this->controller->requestArray = $content;

            if ($content['request']['type'] == 'SimpleUtterance') {
                $this->controller->userCommand = trim($content['request']['command'] ?? '');
                $this->controller->originalUserCommand = trim($content['request']['original_utterance'] ?? '');
            } else {
                if (!is_array($content['request']['payload'])) {
                    $this->controller->userCommand = $content['request']['payload'];
                    $this->controller->originalUserCommand = $content['request']['payload'];
                }
                $this->controller->payload = $content['request']['payload'];
            }
            if (!$this->controller->userCommand) {
                $this->controller->userCommand = $this->controller->originalUserCommand;
            }

            $this->session = $content['session'];

            $userId = null;
            $this->isState = false;
            if (mmApp::$params['y_isAuthUser']) {
                if (isset($this->session['user'], $this->session['user']['user_id'])) {
                    $userId = $this->session['user']['user_id'];
                    $this->isState = true;
                    $this->controller->userToken = $this->session['user']['access_token'] ?? null;
                }
            }

            if ($userId == null) {
                if (isset($this->session['application'], $this->session['application']['application_id'])) {
                    $userId = $this->session['application']['application_id'];
                } else {
                    $userId = $this->session['user_id'];
                }
            }
            $this->controller->userId = $userId;
            mmApp::$params['user_id'] = $this->controller->userId;
            $this->controller->nlu->setNlu($content['request']['nlu'] ?? []);

            $this->controller->userMeta = $content['meta'] ?? [];
            $this->controller->messageId = $this->session['message_id'];

            if (isset($content['state'])) {
                if (isset($content['state']['user'])) {
                    $this->controller->state = $content['state']['user'];
                    $this->stateName = 'user_state_update';
                } elseif (isset($content['state']['session'])) {
                    $this->controller->state = $content['state']['session'];
                    $this->stateName = 'session_state';
                }
            }

            mmApp::$params['app_id'] = $this->session['skill_id'];
            if (isset($this->controller->userMeta['interfaces']['screen'])) {
                $this->controller->isScreen = true;
            } else {
                $this->controller->isScreen = false;
            }
            /**
             * Раз в какое-то время Яндекс отправляет запрос ping, для проверки корректности работы навыка.
             * @see (https://yandex.ru/dev/dialogs/alice/doc/health-check-docpage/) Смотри тут
             */
            if ($this->controller->originalUserCommand == 'ping') {
                $this->controller->text = 'pong';
                echo $this->getContext();
                die();
            }
            return true;
        } else {
            $this->error = 'Alisa:init(): Отправлен пустой запрос!';
        }
        return false;
    }

    /**
     * Отправка ответа пользователю.
     *
     * @return string
     * @see TemplateTypeModel::getContext() Смотри тут
     * @api
     */
    public function getContext(): string
    {
        $result = [];
        if ($this->controller->isAuth && $this->controller->userToken === null) {
            $result['start_account_linking'] = function () {
            };
        } else {
            if (count($this->controller->sound->sounds) || $this->controller->sound->isUsedStandardSound) {
                $this->controller->tts = $this->controller->sound->getSounds($this->controller->tts);
            }
            $result['response'] = $this->getResponse();
        }

        if ($this->isState || $this->isUsedLocalStorage) {
            if ($this->isUsedLocalStorage && $this->controller->userData) {
                $result[$this->stateName] = $this->controller->userData;
            } elseif ($this->controller->state) {
                $result[$this->stateName] = $this->controller->state;
            }
        }
        $result['version'] = self::VERSION;
        $timeEnd = $this->getProcessingTime();
        if ($timeEnd >= self::MAX_TIME_REQUEST) {
            $this->error = "Alisa:getContext(): Превышено ограничение на отправку ответа. Время ответа составило: {$timeEnd} сек.";
        }
        return json_encode($result);
    }

    public function getLocalStorage(): ?array
    {
        return $this->controller->state;
    }

    public function isLocalStorage(): bool
    {
        return $this->controller->state !== null;
    }
}
