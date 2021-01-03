<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\card\types;

use MM\bot\components\button\Buttons;
use MM\bot\components\image\Image;

/**
 * Класс отвечающий за отображение карточки в Сбер SmartApp
 * Class SmartAppCard
 * @package bot\components\button\types
 */
class SmartAppCard extends TemplateCardTypes
{
    /**
     * Получение элементов для карточки
     *
     * @param Image $image Объект с картинкой
     * @return array
     * @private
     */
    protected function getCardItem(Image $image): array
    {
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
        return $cardItem;
    }

    public function getCard(bool $isOne): ?array
    {
        $countImage = count($this->images);
        if ($countImage) {
            if ($isOne) {
                $card = [
                    'can_be_disabled' => false,
                    'type' => 'gallery_card'
                ];
                $card['items'] = [
                    $this->getCardItem($this->images[0])
                ];
                return ['card' => $card];
            } else {
                $card = [
                    'can_be_disabled' => false,
                    'type' => 'gallery_card',
                    'items' => []
                ];
                foreach ($this->images as $image) {

                    $card['items'][] = $this->getCardItem($image);
                }
                return ['card' => $card];
            }
        }
        return null;
    }
}
