<?php

namespace MM\bot\components\button\types;

/**
 * Класс отвечающий за отображение кнопок в Телеграме
 * Class TelegramButton
 * @package bot\components\button\types
 */
class TelegramButton extends TemplateButtonTypes
{
    /**
     * Получение массива с кнопками для ответа пользователю.
     *
     * @return array
     * @api
     */
    public function getButtons(): array
    {
        $object = [];
        $inlines = [];
        $reply = [];
        foreach ($this->buttons as $button) {
            if ($button->url) {
                $inline = [
                    'text' => $button->title,
                    'url' => $button->url
                ];
                if ($button->payload) {
                    $inline['callback_data'] = $button->payload;
                    $inlines[] = $inline;
                }
            } else {
                $reply[] = [$button->title];
            }
        }
        $rCount = count($reply);
        $rInline = count($inlines);
        if ($rCount || $rInline) {
            if ($rInline) {
                $object['inline_keyboard'] = $inlines;
            }
            if ($rCount) {
                $object['keyboard'] = $reply;
            }
        } else {
            // Удаляем клавиатуру из-за ненадобности
            $object = ['remove_keyboard' => true];
        }
        return $object;
    }
}
