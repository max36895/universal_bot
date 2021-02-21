<?php

namespace MM\bot\components\sound\types;

/**
 * Class TemplateSoundTypes
 * @package bot\components\sound\types
 *
 * Шаблонный класс для обработки звуков.
 * Нужен для воспроизведения звуков в ответе пользователю.
 */
abstract class TemplateSoundTypes
{
    /**
     * Получение звуков, которые необходимо воспроизвести или отправить.
     * В случае Алисы, Маруси и Сбер это tts.
     *
     * @param array|null $sounds Массив звуков.
     * @param string $text Исходный текст.
     * @return mixed
     */
    public abstract function getSounds(?array $sounds, string $text);
}
