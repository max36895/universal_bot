<?php

namespace MM\bot\components\card\types;


use Exception;
use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;
use MM\bot\models\ImageTokens;

/**
 * Класс отвечающий за отображение карточки в Алисе.
 * Class AlisaCard
 * @package bot\components\card\types
 */
class AlisaCard extends TemplateCardTypes
{
    public const ALISA_CARD_BIG_IMAGE = 'BigImage';
    public const ALISA_CARD_ITEMS_LIST = 'ItemsList';
    public const ALISA_MAX_IMAGES = 5;
    public const ALISA_MAX_GALLERY_IMAGES = 7;

    /**
     * Получаем элемент карточки
     * @private
     */
    protected function getItem(): array
    {
        $items = [];
        $maxCount = $this->isUsedGallery ? self::ALISA_MAX_GALLERY_IMAGES : self::ALISA_MAX_IMAGES;
        $images = array_slice($this->images, 0, $maxCount);
        foreach ($images as $image) {
            $button = null;
            if (!$this->isUsedGallery) {
                $button = $image->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                if (empty($button)) {
                    $button = null;
                }
            }
            if (!$image->imageToken) {
                if ($image->imageDir) {
                    $mImage = new ImageTokens();
                    $mImage->type = ImageTokens::T_ALISA;
                    $mImage->path = $image->imageDir;
                    $image->imageToken = $mImage->getToken();
                }
            }
            $item = [
                'title' => Text::resize($image->title, 128),
            ];
            if (!$this->isUsedGallery) {
                $item['description'] = Text::resize($image->desc, 256);
            }
            if ($image->imageToken) {
                $item['image_id'] = $image->imageToken;
            }
            if ($button && !$this->isUsedGallery) {
                $item['button'] = $button;
            }
            $items[] = $item;
        }
        return $items;
    }

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
        $this->button->type = Buttons::T_ALISA_CARD_BUTTON;
        $countImage = count($this->images);
        if ($countImage) {
            if ($isOne) {
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $mImage = new ImageTokens();
                        $mImage->type = ImageTokens::T_ALISA;
                        $mImage->path = $this->images[0]->imageDir;
                        $this->images[0]->imageToken = $mImage->getToken();
                    }
                }
                if ($this->images[0]->imageToken) {
                    $button = $this->images[0]->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                    if (empty($button)) {
                        $button = $this->button->getButtons();
                    }
                    $object = [
                        'type' => self::ALISA_CARD_BIG_IMAGE,
                        'image_id' => $this->images[0]->imageToken,
                        'title' => Text::resize($this->images[0]->title, 128),
                        'description' => Text::resize($this->images[0]->desc, 256)
                    ];
                    if (!empty($button)) {
                        $object['button'] = $button;
                    }
                    return $object;
                }
            } else {
                if ($this->isUsedGallery) {
                    $object = [
                        'type' => 'ImageGallery',
                    ];
                    $object['items'] = $this->getItem();
                    return $object;
                } else {
                    $object = [
                        'type' => self::ALISA_CARD_ITEMS_LIST,
                        'header' => [
                            'text' => Text::resize($this->title, 64)
                        ]
                    ];
                    $object['items'] = $this->getItem();
                    $btn = $this->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                    if ($btn) {
                        $object['footer'] = [
                            'text' => $btn['text'],
                            'button' => $btn
                        ];
                    }
                    return $object;
                }
            }
        }
        return [];
    }
}
