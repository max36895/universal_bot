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
use MM\bot\core\api\TelegramRequest;
use MM\bot\core\mmApp;

/**
 * Class Telegram
 * @package bot\core\types
 * @see TemplateTypeModel
 */
class Telegram extends TemplateTypeModel
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
             * @see (https://core.telegram.org/bots/api#getting-updates)
             *  - @var int update_id: Уникальный идентификатор обновления. Обновление идентификаторов начинается с определенного положительного числа и последовательно увеличивается. Этот идентификатор становится особенно удобным, если вы используете Webhooks, так как он позволяет игнорировать повторяющиеся обновления или восстанавливать правильную последовательность обновлений, если они выходят из строя. Если нет новых обновлений хотя бы в течение недели, то идентификатор следующего обновления будет выбран случайным образом, а не последовательно.
             *  - @var array message: Новое входящее сообщение любого вида-текст, фотография, наклейка и т.д.
             * @see (https://core.telegram.org/bots/api#message)
             *      - @var int message_id
             *      - @var array from
             *          - @var int id
             *          - @var bool is_bot
             *          - @var string first_name
             *          - @var string last_name
             *          - @var string username
             *          - @var string language_code
             *      - @var array chat
             *          - @var int id
             *          - @var string first_name
             *          - @var string last_name
             *          - @var string username
             *          - @var string type
             *      - @var int date
             *      - @var string text
             *  -@var array edited_message: Новое входящее сообщение любого вида-текст, фотография, наклейка и т.д. @see message
             *  -@var array channel_post: Новая версия сообщения, которая известна боту и была отредактирована @see message
             *  -@var array edited_channel_post: Новый входящий пост канала любого рода-текст, фото, наклейка и т.д. @see message
             *  -@var array inline_query: Новый входящий встроенный запрос. @see (https://core.telegram.org/bots/api#inlinequery)
             *  -@var array chosen_inline_result: Результат встроенного запроса, который был выбран пользователем и отправлен его партнеру по чату. Пожалуйста, ознакомьтесь с нашей документацией по сбору обратной связи для получения подробной информации о том, как включить эти обновления для вашего бота. @see (https://core.telegram.org/bots/api#choseninlineresult)
             *  -@var array callback_query: Новый входящий запрос обратного вызова. @see (https://core.telegram.org/bots/api#callbackquery)
             *  -@var array shipping_query: Новый входящий запрос на доставку. Только для счетов-фактур с гибкой ценой. @see (https://core.telegram.org/bots/api#shippingquery)
             *  -@var array pre_checkout_query: Новый входящий запрос предварительной проверки. Содержит полную информацию о кассе. @see (https://core.telegram.org/bots/api#precheckoutquery)
             *  -@var array poll: Новое состояние опроса. Боты получают только обновления о остановленных опросах и опросах, которые отправляются ботом. @see (https://core.telegram.org/bots/api#poll)
             *  -@var array poll_answer: Пользователь изменил свой ответ в неанонимном опросе. Боты получают новые голоса только в опросах, которые были отправлены самим ботом. @see (https://core.telegram.org/bots/api#poll_answer)
             */
            $content = json_decode($content, true);
            $this->controller = &$controller;
            $this->controller->requestArray = $content;

            if (isset($content['message'])) {
                $this->controller->userId = $content['message']['chat']['id'];
                mmApp::$params['user_id'] = $this->controller->userId;
                $this->controller->userCommand = trim(mb_strtolower($content['message']['text']));
                $this->controller->originalUserCommand = $content['message']['text'];
                $this->controller->messageId = $content['message']['message_id'];
                $thisUser = [
                    'thisUser' => [
                        'username' => $content['message']['chat']['username'] ?? null,
                        'first_name' => $content['message']['chat']['first_name'] ?? null,
                        'last_name' => $content['message']['chat']['last_name'] ?? null,
                    ]
                ];
                $this->controller->nlu->setNlu($thisUser);
                return true;
            }
        } else {
            $this->error = 'Telegram:init(): Отправлен пустой запрос!';
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
            $telegramApi = new TelegramRequest();
            $params = [];
            $keyboard = $this->controller->buttons->getButtonJson(Buttons::T_TELEGRAM_BUTTONS);
            if ($keyboard) {
                $params['reply_markup'] = $keyboard;
            }
            $params['parse_mode'] = 'markdown';

            $telegramApi->sendMessage($this->controller->userId, $this->controller->text, $params);

            if (count($this->controller->card->images)) {
                $res = $this->controller->card->getCards();
                if (count($res)) {
                    $telegramApi->sendPoll($this->controller->userId, $res['question'], $res['params']);
                }
            }

            if (count($this->controller->sound->sounds)) {
                $this->controller->sound->getSounds($this->controller->tts);
            }
        }
        return 'ok';
    }
}
