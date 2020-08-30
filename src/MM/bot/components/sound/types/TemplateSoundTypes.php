<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\sound\types;

/**
 * Class TemplateSoundTypes
 * @package bot\components\sound\types
 *
 * Шаблонный класс для второстепенных классов.
 * Нужен для воспроизведения звуков в ответе пользователю.
 */
abstract class TemplateSoundTypes
{
    /**
     * Получить звуки, которые необходимо отобразить в приложении.
     * В случае Алисы это tts.
     *
     * @param array|null $sounds Массив звуков.
     * @param string $text Исходный текст.
     * @return mixed
     */
    public abstract function getSounds(?array $sounds, string $text);
}
