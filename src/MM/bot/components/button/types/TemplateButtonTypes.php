<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button\types;

use MM\bot\components\button\Button;


/**
 * Class TemplateButtonTypes
 * @package bot\components\button\types
 *
 * Шаблонный класс для второстепенных классов.
 * Нужен для отображения кнопок в ответе пользователю.
 */
abstract class TemplateButtonTypes
{
    /**
     * Массив кнопок.
     * @var Button[]|null $buttons Массив кнопок.
     */
    public $buttons;

    /**
     * Получить массив с кнопками для ответа пользователю.
     *
     * @return array
     */
    public abstract function getButtons(): array;
}
