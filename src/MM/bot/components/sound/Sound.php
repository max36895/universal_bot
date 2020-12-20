<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\sound;


use MM\bot\components\sound\types\AlisaSound;
use MM\bot\components\sound\types\TelegramSound;
use MM\bot\components\sound\types\TemplateSoundTypes;
use MM\bot\components\sound\types\ViberSound;
use MM\bot\components\sound\types\VkSound;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за обработку и корректное воспроизведение звуков, в зависимости от типа приложения.
 * Class Sound
 * @package bot\components\sound
 */
class Sound
{
    /**
     * Массив звуков.
     * @var array $sounds
     */
    public $sounds;
    /**
     * Использование стандартных звуков.
     * Если true - используются стандартные звуки. Актуально для Алисы. По умолчанию true.
     * @var bool $isUsedStandardSound
     */
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
     * Получение корректно поставленные звуков в текст.
     *
     * @param string $text Исходный текст.
     * @param TemplateSoundTypes|null $userSound Пользовательский класс для обработки звуков.
     * @return string|array
     * @api
     */
    public function getSounds(string $text, ?TemplateSoundTypes $userSound = null)
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

            case T_MARUSIA:
                $sound = null;
                break;

            case T_SMARTAPP:
                $sound = null;
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
