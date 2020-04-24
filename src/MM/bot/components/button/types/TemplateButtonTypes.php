<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:07
 */

namespace MM\bot\components\button\types;

use MM\bot\components\button\Button;


/**
 * Class TemplateButtonTypes
 * @package bot\components\button\types
 *
 * Шаблонный класс для второстепенных классов.
 * Нужен для отображения кнопок в ответе пользователю
 *
 * @property Button[] $buttons
 */
abstract class TemplateButtonTypes
{
    public $buttons;

    /**
     * Получить массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public abstract function getButtons(): array;
}
