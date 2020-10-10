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
        $inlines = [];
        $reply = [];
        foreach ($this->buttons as $button) {
            //if ($button->hide == Button::B_BTN) {
                if ($button->url) {
                    $inline = [
                        'text' => $button->title,
                        'url'=>$button->url
                    ];
                    if ($button->payload) {
                        $inline['callback_data'] = $button->payload;
                        $inlines[] = $inline;
                    }
                } else {
                    $reply[] = [$button->title];
                }
            /*} else {
                $inline = [
                    'text' => $button->title
                ];
                if ($button->url) {
                    $inline['url'] = $button->url;
                }
                if ($button->payload) {
                    $inline['callback_data'] = $button->payload;
                    $inlines[] = $inline;
                } elseif (!isset($inline['url'])) {
                    $reply = [$button->title];
                }
            }*/
        }
        $rCount = count($reply);
        $rInline = count($inlines);
        if ($rCount || $rInline) {
            if ($rInline) {
                $objects['inline_keyboard'] = $inlines;
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
