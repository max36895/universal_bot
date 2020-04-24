<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 25.03.2020
 * Time: 16:57
 */

namespace MM\bot\components\sound\types;

/**
 * Class TemplateSoundTypes
 * @package bot\components\sound\types
 */
abstract class TemplateSoundTypes
{
    /**
     * Получить звуки, которые необходимо отобразить в приложении.
     * В случае Алисы это tts
     *
     * @param array|null $sounds
     * @param string $text
     * @return mixed
     */
    public abstract function getSounds(?array $sounds, string $text);
}
