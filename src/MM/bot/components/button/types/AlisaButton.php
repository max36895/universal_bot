<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:08
 */

namespace MM\bot\components\button\types;


use MM\bot\components\standard\Text;

/**
 * Class AlisaButton
 * @package bot\components\button\types
 *
 * @property bool $isCard: True, чтобы получить кнопки для карточки. По умолчанию false
 */
class AlisaButton extends TemplateButtonTypes
{
    public $isCard;

    /**
     * AlisaButton constructor.
     */
    public function __construct()
    {
        $this->isCard = false;
    }

    /**
     * Получить массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public function getButtons(): array
    {
        $objects = [];
        if ($this->isCard) {
            foreach ($this->buttons as $button) {
                if ($button->payload || $button->url) {
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
