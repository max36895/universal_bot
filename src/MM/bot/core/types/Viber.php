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
use MM\bot\core\api\ViberRequest;
use MM\bot\core\mmApp;

/**
 * Class Viber
 * @package bot\core\types
 * @see TemplateTypeModel
 */
class Viber extends TemplateTypeModel
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
             * @see (https://developers.viber.com/docs/api/rest-bot-api/#receive-message-from-user)
             *  - @var string event: Callback type - какое событие вызвало обратный вызов
             *  - @var int timestamp: Время события, которое вызвало обратный вызов
             *  - @var int message_token: Уникальный идентификатор сообщения
             *  - @var array sender|user: Информация о пользователе. Для event='message' придет sender, иначе user
             *      - @var string id: Уникальный идентификатор пользователя Viber отправителя сообщения
             *      - @var string name: Имя отправителя Viber
             *      - @var string avatar: URL-адрес Аватара отправителя
             *      - @var string country:    Код страны из 2 букв отправителя
             *      - @var string language: Язык телефона отправителя. Будет возвращен в соответствии с языком устройства
             *      - @var int api_version: Максимальная версия Viber, которая поддерживается всеми устройствами пользователя
             *  - @var array message: Информация о сообщении
             *      - @var string type: Тип сообщения
             *      - @var string text: Текст сообщения
             *      - @var string media: URL носителя сообщения-может быть image,video, file и url. URL-адреса изображений/видео/файлов будут иметь TTL в течение 1 часа
             *      - @var array location: Координаты местоположения
             *          - @var float lat: Координата lat
             *          - @var float lon: Координата lon
             *      - @var array contact: name - имя пользователя контакта, phone_number - номер телефона контакта и avatar в качестве URL Аватара
             *          - @var string name
             *          - @var string phone_number
             *          - @var string avatar
             *      - @var string tracking_data: Отслеживание данных, отправленных вместе с последним сообщением пользователю
             *      - @var array file_name: Имя файла. Актуально для type='file'
             *      - @var array file_size: Размер файла в байтах. Актуально для type='file'
             *      - @var array duration: Длина видео в секундах. Актуально для type='video'
             *      - @var array sticker_id: Viber наклейка id. Актуально для type='sticker'
             */
            $content = json_decode($content, true);
            $this->controller = &$controller;
            $this->controller->requestArray = $content;

            if (isset($content['message'])) {
                switch ($content['event']) {
                    case 'conversation_started':
                        $this->controller->userId = $content['user']['id'];

                        $this->controller->userCommand = '';
                        $this->controller->messageId = 0;

                        mmApp::$params['viber_api_version'] = $content['user']['api_version'] ?? 2;
                        $name = explode(' ', $content['sender']['name'] ?? '');
                        $thisUser = [
                            'thisUser' => [
                                'username' => $name[0] ?? null,
                                'first_name' => $name[1] ?? null,
                                'last_name' => $name[2] ?? null,
                            ]
                        ];
                        $this->controller->nlu->setNlu($thisUser);
                        return true;
                        break;
                    case 'message':
                        $this->controller->userId = $content['sender']['id'];
                        mmApp::$params['user_id'] = $this->controller->userId;
                        $this->controller->userCommand = trim(mb_strtolower($content['message']['text'] ?? ''));
                        $this->controller->originalUserCommand = $content['message']['text'] ?? '';
                        $this->controller->messageId = $content['message_token'];

                        mmApp::$params['viber_api_version'] = $content['sender']['api_version'] ?? 2;

                        $name = explode(' ', $content['sender']['name'] ?? '');
                        $thisUser = [
                            'thisUser' => [
                                'username' => $name[0] ?? null,
                                'first_name' => $name[1] ?? null,
                                'last_name' => $name[2] ?? null,
                            ]
                        ];
                        $this->controller->nlu->setNlu($thisUser);
                        return true;
                        break;
                }
            }
        } else {
            $this->error = 'Viber:init(): Отправлен пустой запрос!';
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
            $viberApi = new ViberRequest();
            $params = [];
            $keyboard = $this->controller->buttons->getButtons(Buttons::T_VIBER_BUTTONS);
            if ($keyboard) {
                $params['keyboard'] = $keyboard;
                $params['keyboard']['Type'] = 'keyboard';
            }

            $viberApi->sendMessage($this->controller->userId, mmApp::$params['viber_sender'], $this->controller->text, $params);

            if (count($this->controller->card->images)) {
                $res = $this->controller->card->getCards();
                if (count($res)) {
                    $viberApi->richMedia($this->controller->userId, $res);
                }
            }

            if (count($this->controller->sound->sounds)) {
                $this->controller->sound->getSounds($this->controller->tts);
            }
        }
        return 'ok';
    }
}
