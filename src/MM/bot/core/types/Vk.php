<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 11.03.2020
 * Time: 12:24
 */

namespace MM\bot\core\types;


use MM\bot\components\button\Buttons;
use MM\bot\controller\BotController;
use MM\bot\core\api\VkRequest;
use MM\bot\core\mmApp;

/**
 * Class Vk
 * @package bot\core\types
 * @see TemplateTypeModel
 */
class Vk extends TemplateTypeModel
{
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
            /**
             * @var array $content
             *  - @var string type:
             *  - @var array object:
             *      - @var array message
             *          - @var int date
             *          - @var int from_id
             *          - @var int id
             *          - @var int out
             *          - @var int peer_id
             *          - @var string text
             *          - @var int conversation_message_id
             *          - @var array fwd_messages
             *          - @var bool important
             *          - @var int random_id
             *          - @var array attachments
             *          - @var bool is_hidden
             *          - @var
             *      - @var array clientInfo
             *          - @var array button_actions
             *          - @var bool keyboard
             *          - @var bool inline_keyboard
             *          - @var int lang_id
             *  - @var string group_id:
             *  - @var string event_id:
             *  - @var string secret:
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
     * Отправка ответа пользователю
     *
     * @return string
     * @see TemplateTypeModel::getContext()
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
