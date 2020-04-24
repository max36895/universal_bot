<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 15:53
 */

namespace MM\bot\components\card\types;

use MM\bot\components\button\Buttons;
use MM\bot\components\image\Image;

/**
 * Class TemplateCardTypes
 * @package bot\components\card\types
 * @property Image[] $images: Массив изображений или элементов для карточки
 * @see Buttons
 * @property Buttons $button: Кнопка для карточки
 * @property string $title: Заголовок для карточки
 */
abstract class TemplateCardTypes
{
    public $images;
    public $button;
    public $title;

    /**
     * @param bool $isOne
     * @return mixed
     */
    public abstract function getCard(bool $isOne);
}
