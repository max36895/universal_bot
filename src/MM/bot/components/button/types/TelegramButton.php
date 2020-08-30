<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button\types;


use MM\bot\components\button\Button;

/**
 * Класс отвечающий за отображение кнопок в Телеграме
 * Class TelegramButton
 * @package bot\components\button\types
 */
class TelegramButton extends TemplateButtonTypes
{
    /**
     * Получить массив с кнопками для ответа пользователю.
     *
     * @return array
     * @api
     */
    public function getButtons(): array
    {
        $objects = [];
        $inline = [];
        $reply = [];
        foreach ($this->buttons as $button) {
            if ($button->hide == Button::B_BTN) {
                if ($button->url) {
                    $i = [
                        'text' => $button->title
                    ];
                    if ($button->url) {
                        $i['url'] = $button->url;
                    }
                    if ($button->payload) {
                        $i['callback_data'] = $button->payload;
                        $inline[] = $i;
                    } elseif (!isset($i['url'])) {
                        $reply[] = [$button->title];
                    }
                } else {
                    $reply[] = [$button->title];
                }
            } else {
                $i = [
                    'text' => $button->title
                ];
                if ($button->url) {
                    $i['url'] = $button->url;
                }
                if ($button->payload) {
                    $i['callback_data'] = $button->payload;
                    $inline[] = $i;
                } elseif (!isset($i['url'])) {
                    $reply = [$button->title];
                }
            }
        }
        $rCount = count($reply);
        $rInline = count($inline);
        if ($rCount || $rInline) {
            if ($rInline) {
                $objects['inline_keyboard'] = $inline;
            }
            if ($rCount) {
                $objects['keyboard'] = $reply;
            }
        } else {
            // Удаляем клавиатуру из-за ненадобности
            $objects = ['remove_keyboard' => true];
        }
        return $objects;
    }
}
