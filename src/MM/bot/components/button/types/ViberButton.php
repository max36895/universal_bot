<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button\types;


use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отображение кнопок в Viber
 * Class ViberButton
 * @package bot\components\button\types
 */
class ViberButton extends TemplateButtonTypes
{
    const T_REPLY = 'reply';
    const T_OPEN_URL = 'open-url';
    const T_LOCATION_PICKER = 'location-picker';
    const T_SHARE_PHONE = 'share-phone';
    const T_NONE = 'none';

    /**
     * Получение массива с кнопками для ответа пользователю.
     *
     * @return array
     * @api
     */
    public function getButtons(): array
    {
        $object = [];
        $buttons = [];
        foreach ($this->buttons as $button) {
            $btn = [];
            if ($button->url) {
                $btn['ActionType'] = self::T_OPEN_URL;
                $btn['ActionBody'] = $button->url;
            } else {
                $btn['ActionType'] = self::T_REPLY;
                $btn['ActionBody'] = $button->title;
            }
            $btn['Text'] = $button->title;
            $btn = mmApp::arrayMerge($btn, $button->options);

            $buttons[] = $btn;
        }

        if (count($buttons)) {
            $object = [
                'DefaultHeight' => true,
                'BgColor' => '#FFFFFF',
                'Buttons' => $buttons
            ];
        }
        return $object;
    }
}
