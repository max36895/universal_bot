<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:08
 */

namespace MM\bot\components\button\types;


use MM\bot\core\mmApp;

/**
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
     * Получить массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public function getButtons(): array
    {
        $objects = [];
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
            $objects = [
                'DefaultHeight' => true,
                'BgColor' => '#FFFFFF',
                'Buttons' => $buttons
            ];
        }
        return $objects;
    }
}
