<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 08.03.2020
 * Time: 12:24
 */

namespace MM\bot\components\sound;


use MM\bot\components\sound\types\AlisaSound;
use MM\bot\components\sound\types\TelegramSound;
use MM\bot\components\sound\types\TemplateSoundTypes;
use MM\bot\components\sound\types\ViberSound;
use MM\bot\components\sound\types\VkSound;
use MM\bot\core\mmApp;

/**
 * Class Sound
 * @package bot\components\sound
 *
 * @property array $sounds: Массив звуков
 * @property bool $isUsedStandardSound: True, если использовать стандартные звуки. Актуально для Алисы
 */
class Sound
{
    public $sounds;
    public $isUsedStandardSound;

    /**
     * Sound constructor.
     */
    public function __construct()
    {
        $this->sounds = [];
        $this->isUsedStandardSound = true;
    }

    /**
     * Получить корректно поставленные звуки в текст
     *
     * @param string $text : Исходный текст
     * @param TemplateSoundTypes|null $userSound: Пользовательский класс для обработки звуков
     * @return string|array
     */
    public function getSounds($text, $userSound = null)
    {
        $sound = null;
        switch (mmApp::$appType) {
            case T_ALISA:
                $sound = new AlisaSound();
                $sound->isUsedStandardSound = $this->isUsedStandardSound;
                break;

            case T_VK:
                $sound = new VkSound();
                break;

            case T_TELEGRAM:
                $sound = new TelegramSound();
                break;

            case T_VIBER:
                $sound = new ViberSound();
                break;

            case T_USER_APP:
                $sound = $userSound;
                break;
        }
        if ($sound) {
            return $sound->getSounds($this->sounds, $text);
        }
        return $text;
    }
}
