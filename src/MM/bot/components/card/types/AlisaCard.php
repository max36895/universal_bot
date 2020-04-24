<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 15:50
 */

namespace MM\bot\components\card\types;


use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;
use MM\bot\models\ImageTokens;

/**
 * Class AlisaCard
 * @package bot\components\card\types
 */
class AlisaCard extends TemplateCardTypes
{
    public const ALISA_CARD_BIG_IMAGE = 'BigImage';
    public const ALISA_CARD_ITEMS_LIST = 'ItemsList';
    public const ALISA_MAX_IMAGES = 5;

    /**
     * @param bool $isOne : True, если в любом случае использовать 1 картинку
     * @return array
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $this->button->type = Buttons::T_ALISA_CARD_BUTTON;
        $countImage = count($this->images);
        if ($countImage) {
            if ($countImage === 1 || $isOne) {
                $button = $this->images[0]->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                if (!count($button)) {
                    $button = $this->button->getButtons();
                }
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $mImage = new ImageTokens();
                        $mImage->type = ImageTokens::T_ALISA;
                        $this->images[0]->imageToken = $mImage->getToken();
                    }
                }
                if ($this->images[0]->imageToken) {
                    $object = [
                        'type' => self::ALISA_CARD_BIG_IMAGE,
                        'image_id' => $this->images[0]->imageToken,
                        'title' => Text::resize($this->images[0]->title, 128),
                        'description' => Text::resize($this->images[0]->desc, 256)
                    ];
                    if (count($button)) {
                        $object['button'] = $button[0];
                    }
                }
            } else {
                $tmp = [
                    'type' => self::ALISA_CARD_ITEMS_LIST,
                    'header' => [
                        'text' => Text::resize($this->title, 64)
                    ]
                ];
                $items = [];
                foreach ($this->images as $image) {
                    if (count($items) <= self::ALISA_MAX_IMAGES) {
                        $button = $image->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                        if (!count($button)) {
                            $button = null;
                        }
                        if (!$image->imageToken) {
                            if ($image->imageDir) {
                                $mImage = new ImageTokens();
                                $mImage->type = ImageTokens::T_ALISA;
                                $image->imageToken = $mImage->getToken();
                            }
                        }
                        //if ($image->imageToken !== null) {
                        $item = [
                            'title' => Text::resize($image->title, 128),
                            'description' => Text::resize($image->desc, 256),
                        ];
                        if ($image->imageToken) {
                            $item['image_id'] = $image->imageToken;
                        }
                        if ($button) {
                            $item['button'] = $button;
                        }
                        $items[] = $item;
                    }
                    //}
                }
                $tmp['items'] = $items;
                $items = null;
                $btn = $this->button->getButtons();
                if (count($btn)) {
                    $tmp['footer'] = [
                        'text' => $btn[0]['text'],
                        'button' => $btn[0]
                    ];
                }
                $object = $tmp;
            }
        }
        return $object;
    }
}
