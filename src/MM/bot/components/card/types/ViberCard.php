<?php

namespace MM\bot\components\card\types;


use MM\bot\components\button\Buttons;
use MM\bot\components\image\Image;

/**
 * Класс отвечающий за отображение карточки в Viber.
 * Class ViberCard
 * @package bot\components\card\types
 */
class ViberCard extends TemplateCardTypes
{
    /**
     * Получение элемента карточки.
     * @param Image $image Объект с изображением
     * @param int $countImage Количество изображений
     * @return array
     */
    protected function getElement(Image $image, int $countImage = 1): array
    {
        if (!$image->imageToken) {
            if ($image->imageDir) {
                $image->imageToken = $image->imageDir;
            }
        }

        $element = [
            'Columns' => $countImage,
            'Rows' => 6,
        ];
        if ($image->imageToken) {
            $element['Image'] = $image->imageToken;
        }
        $btn = $image->button->getButtons(Buttons::T_VIBER_BUTTONS);
        if (isset($btn['Buttons'])) {
            $element = array_merge($element, $btn['Buttons'][0]);
            $element['Text'] = "<font color=#000><b>{$image->title}</b></font><font color=#000>{$image->desc}</font>";
        }
        return $element;
    }

    /**
     * Получение карточки для отображения пользователю.
     *
     * @param bool $isOne True, если в любом случае отобразить 1 элемент карточки
     * @return array
     * @api
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $countImage = count($this->images);
        if ($countImage > 7) {
            $countImage = 7;
        }
        if ($countImage) {
            if ($countImage === 1 || $isOne) {
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $this->images[0]->imageToken = $this->images[0]->imageDir;
                    }
                }
                if ($this->images[0]->imageToken) {
                    return $this->getElement($this->images[0]);
                }
            } else {
                foreach ($this->images as $image) {
                    if (count($object) <= $countImage) {
                        $object[] = $this->getElement($image, $countImage);
                    }
                }
            }
        }
        return $object;
    }
}
