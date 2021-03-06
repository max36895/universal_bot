<?php
/**
 * Пример с авторизацией в навыке.
 * Корректная авторизация работает в Алисе, при правильно заполненной конфигурации в кабинете разработчика навыка.
 *
 * Class AuthController
 */
class AuthController extends MM\bot\controller\BotController
{
    public function action($intentName): void
    {
        switch ($intentName) {
            case WELCOME_INTENT_NAME:
                $this->text = 'Привет';
                $this->buttons->btns = ['Пример кнопки галереи'];
                $this->buttons->links = ['Пример ссылки для изображения'];
                break;

            case HELP_INTENT_NAME:
                $this->text = 'Помощь';
                break;

            case 'auth':
                $this->isAuth = true;
                $this->text = 'Авторизация происходит для Алисы!';
                break;

            case 'by':
                $this->text = 'Пока пока!';
                $this->isEnd = true;
                break;

            default:
                $this->text = 'Команда не найдена!';
                break;
        }
    }
}
