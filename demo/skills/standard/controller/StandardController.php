<?php
/**
 * Стандартный пример приложения.
 * Отвечает на команды:
 *  - Привет
 *  - Пока
 *  - Список
 *  - Карточка
 *
 * Class StandardController
 */
class StandardController extends MM\bot\controller\BotController
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

            case 'bigImage':
                $this->text = '';
                $this->tts = 'Большая картинка';
                $this->card->add('565656/78878', 'Заголовок изображения', 'Описание изображения');
                break;

            case 'list':
                $this->tts = 'Галерея из нескольких изображений';
                $this->card->title = 'Галерея';
                $this->card->add('565656/78878', 'Элемент с картинкой"', 'Описание изображения');
                $this->card->add(null, 'Элемент без изображения', 'Описание изображения');
                $this->card->button->addBtn('Текст в footer');
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
