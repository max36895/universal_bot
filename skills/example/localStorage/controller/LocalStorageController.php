<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 29.08.2020
 * Time: 11:35
 */

class LocalStorageController extends MM\bot\controller\BotController
{
    public function action($intentName): void
    {
        switch ($intentName) {
            case WELCOME_INTENT_NAME:
                $this->text = 'Привет';
                $this->buttons->btn = ['Пример кнопки галереи'];
                $this->buttons->link = ['Пример ссылки для картинки'];
                break;

            case HELP_INTENT_NAME:
                $this->text = 'Помощь';
                break;

            case 'bigImage':
                $this->text = '';
                $this->tts = 'Большая картинка';
                $this->card->add('565656/78878', 'Заголовок картинки', 'Описание картинки');
                break;

            case 'list':
                $this->tts = 'Галерея из нескольких изображений';
                $this->card->title = 'Галерея';
                $this->card->add('565656/78878', 'Элемент с картинкой"', 'Описание картинки');
                $this->card->add(null, 'Элемент без картинки', 'Описание картинки');
                $this->card->button->addBtn('Текст в footer');
                break;

            case 'save':
                $this->text = 'Сохранено!';
                $this->userData = [
                    'userId' => $this->userId,
                    'saved' => $this->userCommand
                ];
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
