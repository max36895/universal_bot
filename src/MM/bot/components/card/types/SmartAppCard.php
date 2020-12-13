<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\card\types;

use MM\bot\components\button\Buttons;

/**
 * Класс отвечающий за отображение карточки в Сбер SmartApp
 * Class SmartAppCard
 * @package bot\components\button\types
 */
class SmartAppCard extends TemplateCardTypes
{

    public function getCard(bool $isOne): array
    {
        $countImage = count($this->images);
        if ($countImage) {
            if ($isOne) {
                $card = [
                    'can_be_disabled' => false,
                    'type' => 'gallery_card'
                ];
                $cardItem = [
                    'type' => 'media_gallery_item',
                    'top_text' => [
                        'text' => $this->images[0]->title,
                        'typeface' => $this->images[0]->params['topTypeface'] ?? 'footnote1',
                        'text_color' => $this->images[0]->params['topText_color'] ?? 'default',
                        'margins' => $this->images[0]->params['topMargins'] ?? null,
                        'max_lines' => $this->images[0]->params['topMax_lines'] ?? 0,
                    ],
                    'bottom_text' => [
                        'text' => $this->images[0]->desc,
                        'typeface' => $this->images[0]->params['bottomTypeface'] ?? 'caption',
                        'text_color' => $this->images[0]->params['bottomText_color'] ?? 'default',
                        'margins' => $this->images[0]->params['bottomMargins'] ?? null,
                        'max_lines' => $this->images[0]->params['bottomMax_lines'] ?? 0,
                    ],
                    'image' => [
                        'url' => $this->images[0]->imageDir
                    ]
                ];
                $button = $this->images[0]->button->getButtons(Buttons::T_SMARTAPP_BUTTON_CARD);
                if ($button) {
                    $cardItem['bottom_text']['actions'] = $button;
                }
                $card['items'] = [
                    $cardItem
                ];
                return ['card' => $card];
            } else {
                $card = [
                    'can_be_disabled' => false,
                    'type' => 'gallery_card',
                    'items' => []
                ];
                foreach ($this->images as $image) {
                    $cardItem = [
                        'type' => 'media_gallery_item',
                        'top_text' => [
                            'text' => $image->title,
                            'typeface' => $image->params['topTypeface'] ?? 'footnote1',
                            'text_color' => $image->params['topText_color'] ?? 'default',
                            'margins' => $image->params['topMargins'] ?? null,
                            'max_lines' => $image->params['topMax_lines'] ?? 0,
                        ],
                        'bottom_text' => [
                            'text' => $image->desc,
                            'typeface' => $image->params['bottomTypeface'] ?? 'caption',
                            'text_color' => $image->params['bottomText_color'] ?? 'default',
                            'margins' => $image->params['bottomMargins'] ?? null,
                            'max_lines' => $image->params['bottomMax_lines'] ?? 0,
                        ],
                        'image' => [
                            'url' => $image->imageDir
                        ]
                    ];
                    $button = $image->button->getButtons(Buttons::T_SMARTAPP_BUTTON_CARD);
                    if ($button) {
                        $cardItem['bottom_text']['actions'] = $button;
                    }
                    $card['items'][] = $cardItem;
                }
                return ['card' => $card];
            }
        }
        return null;
    }
}