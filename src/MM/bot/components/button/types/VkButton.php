<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:08
 */

namespace MM\bot\components\button\types;


use MM\bot\components\button\Button;
use MM\bot\core\mmApp;

/**
 * Class VkButton
 * @package bot\components\button\types
 */
class VkButton extends TemplateButtonTypes
{
    /**
     * @const string: Название для группы. Использовать следующим способом:
     * $button->payload[VkButton::GROUP_NAME] = <Название_группы>
     * Используется для группировки кнопок
     */
    public const GROUP_NAME = '_group';

    /**
     * Получить массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public function getButtons(): array
    {
        $groups = [];
        $buttons = [];
        $index = 0;
        foreach ($this->buttons as $button) {
            if ($button->type === null) {
                if ($button->hide === Button::B_LINK) {
                    $button->type = Button::VK_TYPE_LINK;
                } else {
                    $button->type = Button::VK_TYPE_TEXT;
                }
            }
            $object = ['type' => $button->type];
            if ($button->url) {
                $object['type'] = Button::VK_TYPE_LINK;
                $object['link'] = $button->url;
            }
            $object['label'] = $button->title;
            if ($button->payload) {
                $object['payload'] = json_encode($button->payload);
            }

            $object = [
                'action' => $object,
            ];
            if (isset($button->payload['color']) && !$button->url) {
                $object['color'] = $button->payload['color'];
            }
            if ($button->type == Button::VK_TYPE_PAY) {
                $object['hash'] = $button->payload['hash'] ?? null;
            }
            $object = mmApp::arrayMerge($object, $button->options);
            if (isset($button->payload[self::GROUP_NAME])) {
                unset($object['payload'][self::GROUP_NAME]);
                if (isset($groups[$button->payload[self::GROUP_NAME]])) {
                    $buttons[$groups[$button->payload[self::GROUP_NAME]]][] = $object;
                } else {
                    $groups[$button->payload[self::GROUP_NAME]] = $index;
                    $buttons[$index] = [$object];
                    $index++;
                }
            } else {
                $buttons[$index] = [$object];
                $index++;
            }
        }
        $oneTime = false;
        if (count($buttons)) {
            $oneTime = true;
        }
        return [
            'one_time' => $oneTime,
            'buttons' => $buttons
        ];
    }
}
