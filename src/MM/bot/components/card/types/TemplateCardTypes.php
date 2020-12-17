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
 * Class TemplateCardTypes
 * @package bot\components\card\types
 *
 * Шаблонный класс для второстепенных классов.
 * Нужен для отображения карточек в ответе пользователю.
 */
abstract class TemplateCardTypes
{
    /**
     * Массив изображений или элементов для карточки.
     * @var Image[]|null $images
     */
    public $images;
    /**
     * Кнопка для карточки.
     * @var Buttons|null $button
     * @see Buttons Смотри тут
     */
    public $button;
    /**
     * Заголовок для карточки.
     * @var string|null $title
     */
    public $title;

    /**
     * Получение карточки для отображения пользователю.
     *
     * @param bool $isOne True, если в любом случае использовать 1 картинку.
     * @return array
     */
    public abstract function getCard(bool $isOne);
}
