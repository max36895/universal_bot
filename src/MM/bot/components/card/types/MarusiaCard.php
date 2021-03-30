<?php

namespace MM\bot\components\card\types;


use Exception;
use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;
use MM\bot\models\ImageTokens;

/**
 * Класс отвечающий за отображение карточки в Марусе.
 * Class MarusiaCard
 * @package bot\components\card\types
 */
class MarusiaCard extends TemplateCardTypes
{
    public const MARUSIA_CARD_BIG_IMAGE = 'BigImage';
    public const MARUSIA_CARD_ITEMS_LIST = 'ItemsList';
    public const MARUSIA_MAX_IMAGES = 5;

    /**
     * Получение карточки для отображения пользователю.
     *
     * @param bool $isOne True, если в любом случае отобразить 1 элемент карточки
     * @return array
     * @throws Exception
     * @api
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $this->button->type = Buttons::T_ALISA_CARD_BUTTON;
        $countImage = count($this->images);
        if ($countImage) {
            if ($isOne) {
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $mImage = new ImageTokens();
                        $mImage->type = ImageTokens::T_ALISA;
                        $this->images[0]->imageToken = $mImage->getToken();
                    }
                }
                if ($this->images[0]->imageToken) {
                    $object = [
                        'type' => self::MARUSIA_CARD_BIG_IMAGE,
                        'image_id' => $this->images[0]->imageToken,
                        'title' => Text::resize($this->images[0]->title, 128),
                        'description' => Text::resize($this->images[0]->desc, 256)
                    ];
                }
            } else {
                $tmp = [
                    'type' => self::MARUSIA_CARD_ITEMS_LIST,
                    'header' => [
                        'text' => Text::resize($this->title, 64)
                    ]
                ];
                $items = [];
                foreach ($this->images as $image) {
                    if (count($items) <= self::MARUSIA_MAX_IMAGES) {
                        if (!$image->imageToken) {
                            if ($image->imageDir) {
                                $mImage = new ImageTokens();
                                $mImage->type = ImageTokens::T_ALISA;
                                $image->imageToken = $mImage->getToken();
                            }
                        }
                        $item = [
                            'title' => Text::resize($image->title, 128),
                            'description' => Text::resize($image->desc, 256),
                        ];
                        if ($image->imageToken) {
                            $item['image_id'] = $image->imageToken;
                        }
                        $items[] = $item;
                    }
                }
                $tmp['items'] = $items;
                $items = null;
                $object = $tmp;
            }
        }
        return $object;
    }
}
