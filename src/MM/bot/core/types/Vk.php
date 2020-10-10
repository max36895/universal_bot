<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\core\types;


use MM\bot\components\button\Buttons;
use MM\bot\controller\BotController;
use MM\bot\api\VkRequest;
use MM\bot\core\mmApp;

/**
 * Класс, отвечающий за корректную инициализацию и отправку ответа для ВКонтакте.
 * Class Vk
 * @package bot\core\types
 * @see TemplateTypeModel Смотри тут
 */
class Vk extends TemplateTypeModel
{
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
            /**
             * array $content
             *  - string type:
             *  - array object:
             *      - array message
             *          - int date
             *          - int from_id
             *          - int id
             *          - int out
             *          - int peer_id
             *          - string text
             *          - int conversation_message_id
             *          - array fwd_messages
             *          - bool important
             *          - int random_id
             *          - array attachments
             *          - bool is_hidden
             *      - array clientInfo
             *          - array button_actions
             *          - bool keyboard
             *          - bool inline_keyboard
             *          - int lang_id
             *  - string group_id:
             *  - string event_id:
             *  - string secret:
             */
            $content = json_decode($content);
            $this->controller = &$controller;
            $this->controller->requestArray = $content;

            switch ($content['type'] ?? null) {
                case 'confirmation':
                    echo mmApp::$params['vk_confirmation_token'];
                    die();
                    break;
                case 'message_new':
                    if (isset($content['object'])) {
                        $object = $content['object'];
                        $this->controller->userId = $object['message']['from_id'];
                        mmApp::$params['user_id'] = $this->controller->userId;
                        $this->controller->userCommand = trim(mb_strtolower($object['message']['text']));
                        $this->controller->originalUserCommand = trim($object['message']['text']);
                        $this->controller->messageId = $object['message']['id'];
                        $this->controller->payload = $object['message']['payload'] ?? null;
                        $user = (new VkRequest())->usersGet($this->controller->userId);
                        if ($user) {
                            $thisUser = [
                                'thisUser' => [
                                    'username' => null,
                                    'first_name' => $user['first_name'] ?? null,
                                    'last_name' => $user['last_name'] ?? null
                                ]
                            ];
                            $this->controller->nlu->setNlu($thisUser);
                        }
                        return true;
                    }
                    return false;
                    break;
                default:
                    $this->error = 'Vk:init(): Некорректный тип данных!';
                    break;
            }
        } else {
            $this->error = 'Vk:init(): Отправлен пустой запрос!';
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
        if ($this->controller->isSend) {
            $keyboard = $this->controller->buttons->getButtonJson(Buttons::T_VK_BUTTONS);
            $params = [];
            if ($keyboard) {
                $params['keyboard'] = $keyboard;
            }
            if (count($this->controller->card->images)) {
                $attach = $this->controller->card->getCards();
                if (isset($attach['type'])) {
                    $params['template'] = $attach;
                } else {
                    $params['attachments'] = $attach;
                }
            }
            if (count($this->controller->sound->sounds)) {
                $attach = $this->controller->sound->getSounds($this->controller->tts);
                $params['attachments'] = array_merge($attach, $params['attachments']);
            }
            $vkApi = new VkRequest();
            $vkApi->messagesSend($this->controller->userId, $this->controller->text, $params);
        }
        return 'ok';
    }
}
