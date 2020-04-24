<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:08
 */

namespace MM\bot\components\button\types;


use MM\bot\components\button\Button;

/**
 * Class TelegramButton
 * @package bot\components\button\types
 */
class TelegramButton extends TemplateButtonTypes
{
    /**
     * Получить массив с кнопками для ответа пользователю
     *
     * @return array
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
