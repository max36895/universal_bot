<?php
use MM\bot\components\button\types\TemplateButtonTypes;

class UserButton extends TemplateButtonTypes
{
    /**
     * Получение массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public function getButtons(): array
    {
        $objects = [];
        foreach ($this->buttons as $button) {
            /*
             * Заполняем массив $object нужными данными
             */
        }
        return $objects;
    }
}
