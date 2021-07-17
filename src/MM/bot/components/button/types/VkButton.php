<?php

namespace MM\bot\components\button\types;


use MM\bot\components\button\Button;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отображение кнопок в ВКонтакте
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
     * Получение массива с кнопками для ответа пользователю.
     *
     * @return array
     * @api
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
            $action = ['type' => $button->type];
            if ($button->url) {
                $action['type'] = Button::VK_TYPE_LINK;
                $action['link'] = $button->url;
            }
            $action['label'] = $button->title;
            if ($button->payload) {
                $action['payload'] = $button->payload;
            }

            $object = [
                'action' => $action,
            ];
            if (isset($button->payload['color']) && !$button->url) {
                $object['color'] = $button->payload['color'];
            }
            if ($button->type === Button::VK_TYPE_PAY) {
                $object['hash'] = $button->payload['hash'] ?? null;
            }
            $object = mmApp::arrayMerge($object, $button->options);
            if (isset($button->options[self::GROUP_NAME])) {
                unset($object[self::GROUP_NAME]);
                if (isset($object['payload'])) {
                    $object['payload'] = json_encode($object['payload']);
                }
                if (isset($groups[$button->options[self::GROUP_NAME]])) {
                    $buttons[$groups[$button->options[self::GROUP_NAME]]][] = $object;
                } else {
                    $groups[$button->options[self::GROUP_NAME]] = $index;
                    $buttons[$index] = [$object];
                    $index++;
                }
            } else {
                if (isset($object['payload'])) {
                    $object['payload'] = json_encode($object['payload']);
                }
                $buttons[$index] = $object;
                $index++;
            }
        }

        return [
            'one_time' => !empty($buttons),
            'buttons' => $buttons
        ];
    }
}
