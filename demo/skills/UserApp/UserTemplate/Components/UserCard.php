<?php

use MM\bot\components\button\Buttons;
use MM\bot\components\card\types\TemplateCardTypes;

require_once __DIR__ . '/UserButton.php';

class UserCard extends TemplateCardTypes
{
    /**
     * Получение массива для отображения карточки/картинки
     *
     * @param bool $isOne True, если отобразить только 1 картинку.
     * @return array
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $countImage = count($this->images);
        if ($countImage > 7) {
            $countImage = 7;
        }
        $userButton = new UserButton();
        if ($countImage) {
            if ($countImage === 1 || $isOne) {
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $this->images[0]->imageToken = $this->images[0]->imageDir;
                    }
                }
                if ($this->images[0]->imageToken) {
                    /*
                     * Заполняем $object необходимыми данными
                     */
                    // Получаем возможные кнопки у карточки
                    $btn = $this->images[0]->button->getButtons(Buttons::T_USER_APP_BUTTONS, $userButton);
                    if ($btn) {
                        // Добавляем кнопки к карточке
                        $object = array_merge($object, $btn[0]);
                    }
                }
            } else {
                foreach ($this->images as $image) {
                    if (!$image->imageToken) {
                        if ($image->imageDir) {
                            $image->imageToken = $image->imageDir;
                        }
                    }
                    $element = [];
                    /*
                     * Заполняем $element необходимыми данными
                     */
                    // Получаем возможные кнопки у карточки
                    $btn = $image->button->getButtons(Buttons::T_USER_APP_BUTTONS, $userButton);
                    if ($btn) {
                        // Добавляем кнопки к карточке
                        $object = array_merge($object, $btn[0]);
                    }
                    $object[] = $element;
                }
            }
        }
        return $object;
    }
}
