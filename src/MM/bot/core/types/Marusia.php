<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 24.07.2020
 * Time: 19:19
 */

namespace MM\bot\core\types;


use MM\bot\components\button\Buttons;
use MM\bot\components\sound\types\AlisaSound;
use MM\bot\components\standard\Text;
use MM\bot\controller\BotController;
use MM\bot\core\mmApp;

/**
 * Class Marusia
 * @package bot\core\types
 * @see TemplateTypeModel
 */
class Marusia extends TemplateTypeModel
{
    /**
     * @const string: Версия Алисы
     */
    private const VERSION = '1.0';
    /**
     * @const float: Максимально время, за которое должен ответить навык
     */
    private const MAX_TIME_REQUEST = 2.8;
    /**
     * @var array $session :
     */
    protected $session;

    /**
     * Генерирование ответа пользователю
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
     * Генерирование информации о сессии
     *
     * @return array
     */
    protected function getSession(): array
    {
        return [
            'session_id' => $this->session['session_id'],
            'message_id' => $this->session['message_id'],
            'user_id' => $this->session['user_id']
        ];
    }

    /**
     * Инициализация параметров
     *
     * @param null|string $content
     * @param BotController $controller
     * @return bool
     * @see TemplateTypeModel::init()
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
                $this->error = 'Marusia::init(): Не корректные данные!';
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
            $userId = $this->session['user_id'];

            $this->controller->userId = $userId;
            mmApp::$params['user_id'] = $this->controller->userId;
            $this->controller->nlu->setNlu($content['request']['nlu'] ?? []);

            $this->controller->userMeta = $content['meta'] ?? [];
            $this->controller->messageId = $this->session['message_id'];

            mmApp::$params['app_id'] = $this->session['skill_id'];
            if (isset($this->controller->userMeta['interfaces']['screen'])) {
                $this->controller->isScreen = true;
            } else {
                $this->controller->isScreen = false;
            }
            return true;
        } else {
            $this->error = 'Marusia:init(): Отправлен пустой запрос!';
        }
        return false;
    }

    /**
     * Отправка ответа пользователю
     *
     * @return string
     * @see TemplateTypeModel::getContext()
     */
    public function getContext(): string
    {
        $result = [];
        $result['response'] = $this->getResponse();
        $result['session'] = $this->getSession();
        $result['version'] = self::VERSION;
        $timeEnd = $this->getProcessingTime();
        if ($timeEnd >= self::MAX_TIME_REQUEST) {
            $this->error = "Marusia:getContext(): Превышено ограничение на отправку ответа. Время ответа составило: {$timeEnd} сек.";
        }
        return json_encode($result);
    }
}