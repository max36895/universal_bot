<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button\types;


use MM\bot\components\standard\Text;

/**
 * Класс отвечающий за отображение кнопок в Алисе
 * Class AlisaButton
 * @package bot\components\button\types
 */
class AlisaButton extends TemplateButtonTypes
{
    /**
     * True, чтобы получить кнопки для карточки. По умолчанию false
     * @var bool $isCard True, чтобы получить кнопки для карточки. По умолчанию false
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
     * Получить массив с кнопками для ответа пользователю.
     *
     * @return array
     * [
     *  - string text: Текст на кнопке.
     *  - string payload: Произвольные данные, которые будут отправлены пр нажатии на кнопку.
     *  - string url: Ссылка по которой будет произведен переход после нажатия на кнопку.
     * ]
     * @api
     */
    public function getButtons(): array
    {
        $objects = [];
        if ($this->isCard) {
            foreach ($this->buttons as $button) {
                $text = Text::resize($button->title, 64);
                if ($text) {
                    $object = [
                        'text' => $text,
                    ];
                    if ($button->payload) {
                        $object['payload'] = $button->payload;
                    }
                    if ($button->url) {
                        $object['url'] = Text::resize($button->url, 1024);
                    }
                    $objects[] = $object;
                }
            }
        } else {
            foreach ($this->buttons as $button) {
                $title = Text::resize($button->title, 64);
                if ($title) {
                    $object = [
                        'title' => $title,
                        'hide' => $button->hide
                    ];
                    if ($button->payload) {
                        $object['payload'] = $button->payload;
                    }
                    if ($button->url) {
                        $object['url'] = Text::resize($button->url, 1024);
                    }
                    $objects[] = $object;
                }
            }
        }
        return $objects;
    }
}
