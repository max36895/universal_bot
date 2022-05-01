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
     * Получаем элемент карточки
     * @private
     */
    protected function getItem(): array
    {
        $items = [];
        $images = array_slice($this->images, 0, self::MARUSIA_MAX_IMAGES);
        foreach ($images as $image) {
            if (!$image->imageToken) {
                if ($image->imageDir) {
                    $mImage = new ImageTokens();
                    $mImage->type = ImageTokens::T_MARUSIA;
                    $mImage->path = $image->imageDir;
                    $image->imageToken = $mImage->getToken();
                }
            }
            if ($image->imageToken) {
                $items[] = ['image_id' => $image->imageToken];
            }
            continue;
            // todo коммент ниже
            $button = $image->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
            if (empty($button)) {
                $button = null;
            }
            $item = [
                'title' => Text::resize($image->title, 128),
                $item['description'] = Text::resize($image->desc, 256)
            ];
            if ($image->imageToken) {
                $item['image_id'] = $image->imageToken;
            }
            if ($button) {
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
                        $mImage->type = ImageTokens::T_MARUSIA;
                        $mImage->path = $this->images[0]->imageDir;
                        $this->images[0]->imageToken = $mImage->getToken();
                    }
                }
                if ($this->images[0]->imageToken) {
                    return [
                        'type' => self::MARUSIA_CARD_BIG_IMAGE,
                        'image_id' => $this->images[0]->imageToken,
                    ];
                    // todo пока маруся не поддерживает такое же отображение как в Алисе, но скорей всего будет поддерживать.
                    $button = $this->images[0]->button->getButtons(Buttons::T_ALISA_CARD_BUTTON);
                    if (empty($button)) {
                        $button = $this->button->getButtons();
                    }
                    $object = [
                        'type' => self::MARUSIA_CARD_BIG_IMAGE,
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
                $object = [
                    'type' => self::MARUSIA_CARD_ITEMS_LIST,
                    // todo расскоментируй когда станет актуально
                    /* 'header' => [
                         'text' => Text::resize($this->title, 64)
                     ]*/
                ];
                $object['items'] = $this->getItem();
                return $object;
                // todo см коммент выше
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
        return [];
    }
}
