<?php

use MM\bot\components\button\Buttons;
use MM\bot\controller\BotController;
use MM\bot\core\mmApp;
use MM\bot\core\types\TemplateTypeModel;

require_once __DIR__ . '/../Components/UserButton.php';
require_once __DIR__ . '/../Components/UserCard.php';
require_once __DIR__ . '/../Components/UserSound.php';

class UserApp extends TemplateTypeModel
{
    /**
     * Инициализация параметров
     *
     * @param null|string $content
     * @param BotController $controller
     * @return bool
     * @see TemplateTypeModel::init() Смотри тут
     */
    public function init(?string $content, BotController &$controller): bool
    {
        if ($content) {
            $content = json_decode($content, true);
            $this->controller = &$controller;
            $this->controller->requestObject = $content;
            /**
             * Инициализация основных параметров приложения
             */
            $this->controller->userCommand = $content['data']['text'];
            $this->controller->originalUserCommand = $content['data']['text'];

            $this->controller->userId = 'Идентификатор пользователя. Берется из $content';
            mmApp::$params['user_id'] = $this->controller->userId;
            return true;
        } else {
            $this->error = 'UserApp:init(): Отправлен пустой запрос!';
        }
        return false;
    }

    /**
     * Отправка ответа пользователю
     *
     * @return string
     * @see TemplateTypeModel::getContext() Смотри тут
     */
    public function getContext(): string
    {
        // Проверяем отправлять ответ пользователю или нет
        if ($this->controller->isSend) {
            /**
             * Отправляем ответ в нужном формате
             */
            $buttonClass = new UserButton();// Класс отвечающий за отображение кнопок. Должен быть унаследован от TemplateButtonTypes
            /*
             * Получение кнопок
             */
            $buttons = $this->controller->buttons->getButtons(Buttons::T_USER_APP_BUTTONS, $buttonClass);

            $cardClass = new UserCard();// Класс отвечающий за отображение карточек. Должен быть унаследован от TemplateCardTypes
            /*
             * Получить информацию о карточке
             */
            $cards = $this->controller->card->getCards($cardClass);

            $soundClass = new UserSound();// Класс отвечающий за отображение звуков. Должен быть унаследован от TemplateSoundTypes
            /*
             * Получить все звуки
             */
            $sounds = $this->controller->sound->getSounds('', $soundClass);
        }
        // Возвращаем json строку или любое нужное значение
        return 'ok';
    }
}
