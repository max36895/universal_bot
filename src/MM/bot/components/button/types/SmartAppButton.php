<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button\types;


use MM\bot\components\standard\Text;

/**
 * Класс отвечающий за отображение кнопок в Сбер SmartApp
 * Class AlisaButton
 * @package bot\components\button\types
 */
class SmartAppButton extends TemplateButtonTypes
{
    /**
     * Использование кнопок для карточки
     * True - получение кнопки для карточки. По умолчанию false
     * @var bool $isCard
     */
    public $isCard;

    /**
     * AlisaButton constructor.
     */
    public function __construct()
    {
        $this->isCard = false;
    }

    /**
     * Получение массива с кнопками для ответа пользователю.
     *
     * @return array
     * [
     *  - string title: Название кнопки, которое отображается в интерфейсе ассистента.
     *  - array action Описывает действие, которое выполнится по нажатию кнопки.
     *      - string text: Текст, который появится на экране. Передается, только в действии типа text.
     *      - array payload: Объект передаётся в сообщении SERVER_ACTION, после нажатия кнопки, тип действия которой задан как server_action.
     *      - string type: Тип действия.
     * Возможные значения:
     * text — по нажатию на кнопку отображается текст, указанный в поле text.
     * server_action — указывайте этот тип чтобы передать в бекэнд приложения сообщение SERVER_ACTION с необходимым объектом server_action.
     * ]
     * @api
     */
    public function getButtons(): array
    {
        $objects = [];
        if ($this->isCard) {
            if ($this->buttons[0]->url) {
                return [
                    'deep_link' => $this->buttons[0]->url,
                    'type' => 'deep_link'
                ];
            } else {
                $text = Text::resize($this->buttons[0]->title, 64);
                if ($text) {
                    return [
                        'text' => $text,
                        'type' => 'deep_link'
                    ];
                }
            }
        } else {
            foreach ($this->buttons as $button) {
                $title = Text::resize($button->title, 64);
                if ($title) {
                    $object = [
                        'title' => $title,
                    ];
                    if ($button->payload) {
                        $object['action'] = [
                            'server_action' => $button->payload,
                            'type' => 'server_action'
                        ];
                    } else {
                        $object['action'] = [
                            'text' => $title,
                            'type' => 'text'
                        ];
                    }
                    $objects[] = $object;
                }
            }
        }
        return $objects;
    }
}
