<?php

namespace MM\bot\components\sound\types;

use Exception;
use MM\bot\components\standard\Text;
use MM\bot\models\SoundTokens;

/**
 * Класс отвечающий за воспроизведение звуков в Марусе.
 * Class MarusiaSound
 * @package bot\components\sound\types
 */
class MarusiaSound extends TemplateSoundTypes
{
    /**
     * Использование стандартных звуков.
     * True - используются стандартные звуки.
     * @var bool $isUsedStandardSound
     */
    public $isUsedStandardSound = true;

    const S_EFFECT_BEHIND_THE_WALL = '<speaker effect="behind_the_wall">';
    const S_EFFECT_HAMSTER = '<speaker effect="hamster">';
    const S_EFFECT_MEGAPHONE = '<speaker effect="megaphone">';
    const S_EFFECT_PITCH_DOWN = '<speaker effect="pitch_down">';
    const S_EFFECT_PSYCHODELIC = '<speaker effect="psychodelic">';
    const S_EFFECT_PULSE = '<speaker effect="pulse">';
    const S_EFFECT_TRAIN_ANNOUNCE = '<speaker effect="train_announce">';
    const S_EFFECT_END = '<speaker effect="-">';

    /** Стандартные звуки.
     * @var array[] $standardSounds
     */
    protected $standardSounds = [
        [
            'key' => '#$game_win$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-win-1">',
                '<speaker audio="marusia-sounds/game-win-2">',
                '<speaker audio="marusia-sounds/game-win-3">',
            ]
        ],
        [
            'key' => '#$game_loss$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-loss-1">',
                '<speaker audio="marusia-sounds/game-loss-2">',
                '<speaker audio="marusia-sounds/game-loss-3">',
            ]
        ],
        [
            'key' => '#$game_boot$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-boot-1">',
            ]
        ],
        [
            'key' => '#$game_coin$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-8-bit-coin-1">',
                '<speaker audio="marusia-sounds/game-8-bit-coin-2">',
            ]
        ],
        [
            'key' => '#$game_ping$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-ping-1">',
            ]
        ],
        [
            'key' => '#$game_fly$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-8-bit-flyby-1">',
            ]
        ],
        [
            'key' => '#$game_gun$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-8-bit-machine-gun-1">',
            ]
        ],
        [
            'key' => '#$game_phone$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-8-bit-phone-1">',
            ]
        ],
        [
            'key' => '#$game_powerup$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/game-powerup-1">',
                '<speaker audio="marusia-sounds/game-powerup-2">',
            ]
        ],

        [
            'key' => '#$nature_wind$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-wind-1">',
                '<speaker audio="marusia-sounds/nature-wind-2">',
            ]
        ],
        [
            'key' => '#$nature_thunder$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-thunder-1">',
                '<speaker audio="marusia-sounds/nature-thunder-2">',
            ]
        ],
        [
            'key' => '#$nature_jungle$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-jungle-1">',
                '<speaker audio="marusia-sounds/nature-jungle-2">',
            ]
        ],
        [
            'key' => '#$nature_rain$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-rain-1">',
                '<speaker audio="marusia-sounds/nature-rain-2">',
            ]
        ],
        [
            'key' => '#$$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-forest-1">',
                '<speaker audio="marusia-sounds/nature-forest-2">',
            ]
        ],
        [
            'key' => '#$nature_sea$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-sea-1">',
                '<speaker audio="marusia-sounds/nature-sea-2">',
            ]
        ],
        [
            'key' => '#$nature_fire$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-fire-1">',
                '<speaker audio="marusia-sounds/nature-fire-2">',
            ]
        ],
        [
            'key' => '#$nature_stream$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/nature-stream-1">',
                '<speaker audio="marusia-sounds/nature-stream-2">',
            ]
        ],
        [
            'key' => '#$thing_chainsaw$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/things-chainsaw-1">',
                '<speaker audio="marusia-sounds/things-explosion-1">',
                '<speaker audio="marusia-sounds/things-water-3">',
                '<speaker audio="marusia-sounds/things-water-1">',
                '<speaker audio="marusia-sounds/things-water-2">',
                '<speaker audio="marusia-sounds/things-switch-1">',
                '<speaker audio="marusia-sounds/things-switch-2">',
                '<speaker audio="marusia-sounds/things-gun-1">',
                '<speaker audio="marusia-sounds/transport-ship-horn-1">',
                '<speaker audio="marusia-sounds/transport-ship-horn-2">',
                '<speaker audio="marusia-sounds/things-door-1">',
                '<speaker audio="marusia-sounds/things-door-2">',
                '<speaker audio="marusia-sounds/things-glass-2">',
                '<speaker audio="marusia-sounds/things-bell-1">',
                '<speaker audio="marusia-sounds/things-bell-2">',
                '<speaker audio="marusia-sounds/things-car-1">',
                '<speaker audio="marusia-sounds/things-car-2">',
                '<speaker audio="marusia-sounds/things-sword-2">',
                '<speaker audio="marusia-sounds/things-sword-1">',
                '<speaker audio="marusia-sounds/things-sword-3">',
                '<speaker audio="marusia-sounds/things-siren-1">',
                '<speaker audio="marusia-sounds/things-siren-2">',
                '<speaker audio="marusia-sounds/things-old-phone-1">',
                '<speaker audio="marusia-sounds/things-old-phone-2">',
                '<speaker audio="marusia-sounds/things-glass-1">',
                '<speaker audio="marusia-sounds/things-construction-2">',
                '<speaker audio="marusia-sounds/things-construction-1">',
                '<speaker audio="marusia-sounds/things-phone-1">',
                '<speaker audio="marusia-sounds/things-phone-2">',
                '<speaker audio="marusia-sounds/things-phone-3">',
                '<speaker audio="marusia-sounds/things-phone-4">',
                '<speaker audio="marusia-sounds/things-phone-5">',
                '<speaker audio="marusia-sounds/things-toilet-1">',
                '<speaker audio="marusia-sounds/things-cuckoo-clock-2">',
                '<speaker audio="marusia-sounds/things-cuckoo-clock-1">',
            ]
        ],
        [
            'key' => '#$animals_all$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/animals-wolf-1">',
                '<speaker audio="marusia-sounds/animals-crow-1">',
                '<speaker audio="marusia-sounds/animals-crow-2">',
                '<speaker audio="marusia-sounds/animals-cow-1">',
                '<speaker audio="marusia-sounds/animals-cow-2">',
                '<speaker audio="marusia-sounds/animals-cow-3">',
                '<speaker audio="marusia-sounds/animals-cat-1">',
                '<speaker audio="marusia-sounds/animals-cat-2">',
                '<speaker audio="marusia-sounds/animals-cat-3">',
                '<speaker audio="marusia-sounds/animals-cat-4">',
                '<speaker audio="marusia-sounds/animals-cat-5">',
                '<speaker audio="marusia-sounds/animals-cuckoo-1">',
                '<speaker audio="marusia-sounds/animals-chicken-1">',
                '<speaker audio="marusia-sounds/animals-lion-1">',
                '<speaker audio="marusia-sounds/animals-lion-2">',
                '<speaker audio="marusia-sounds/animals-horse-1">',
                '<speaker audio="marusia-sounds/animals-horse-2">',
                '<speaker audio="marusia-sounds/animals-horse-galloping-1">',
                '<speaker audio="marusia-sounds/animals-horse-walking-1">',
                '<speaker audio="marusia-sounds/animals-frog-1">',
                '<speaker audio="marusia-sounds/animals-seagull-1">',
                '<speaker audio="marusia-sounds/animals-monkey-1">',
                '<speaker audio="marusia-sounds/animals-sheep-1">',
                '<speaker audio="marusia-sounds/animals-sheep-2">',
                '<speaker audio="marusia-sounds/animals-rooster-1">',
                '<speaker audio="marusia-sounds/animals-elephant-1">',
                '<speaker audio="marusia-sounds/animals-elephant-2">',
                '<speaker audio="marusia-sounds/animals-dog-1">',
                '<speaker audio="marusia-sounds/animals-dog-2">',
                '<speaker audio="marusia-sounds/animals-dog-3">',
                '<speaker audio="marusia-sounds/animals-dog-4">',
                '<speaker audio="marusia-sounds/animals-dog-5">',
                '<speaker audio="marusia-sounds/animals-owl-1">',
                '<speaker audio="marusia-sounds/animals-owl-2">',
            ]
        ],
        [
            'key' => '#$human_all$#',
            'sounds' => [
                '<speaker audio="marusia-sounds/human-cheer-1">',
                '<speaker audio="marusia-sounds/human-cheer-2">',
                '<speaker audio="marusia-sounds/human-kids-1">',
                '<speaker audio="marusia-sounds/human-walking-dead-1">',
                '<speaker audio="marusia-sounds/human-walking-dead-2">',
                '<speaker audio="marusia-sounds/human-walking-dead-3">',
                '<speaker audio="marusia-sounds/human-cough-1">',
                '<speaker audio="marusia-sounds/human-cough-2">',
                '<speaker audio="marusia-sounds/human-laugh-1">',
                '<speaker audio="marusia-sounds/human-laugh-2">',
                '<speaker audio="marusia-sounds/human-laugh-3">',
                '<speaker audio="marusia-sounds/human-laugh-4">',
                '<speaker audio="marusia-sounds/human-laugh-5">',
                '<speaker audio="marusia-sounds/human-crowd-1">',
                '<speaker audio="marusia-sounds/human-crowd-2">',
                '<speaker audio="marusia-sounds/human-crowd-3">',
                '<speaker audio="marusia-sounds/human-crowd-4">',
                '<speaker audio="marusia-sounds/human-crowd-5">',
                '<speaker audio="marusia-sounds/human-crowd-7">',
                '<speaker audio="marusia-sounds/human-crowd-6">',
                '<speaker audio="marusia-sounds/human-sneeze-1">',
                '<speaker audio="marusia-sounds/human-sneeze-2">',
                '<speaker audio="marusia-sounds/human-walking-room-1">',
                '<speaker audio="marusia-sounds/human-walking-snow-1">',
            ]
        ]
    ];

    const S_AUDIO_GAME_BOOT = '#$game_boot$#';
    const S_AUDIO_GAME_8_BIT_COIN = '#$game_coin$#';
    const S_AUDIO_GAME_LOSS = '#$game_loss$#';
    const S_AUDIO_GAME_PING = '#$game_ping$#';
    const S_AUDIO_GAME_WIN = '#$game_win$#';
    const S_AUDIO_GAME_8_BIT_FLYBY = '#$game_fly$#';
    const S_AUDIO_GAME_8_BIT_MACHINE_GUN = '#$game_gun$#';
    const S_AUDIO_GAME_8_BIT_PHONE = '#$games_phone$#';
    const S_AUDIO_GAME_POWERUP = '#$games_powerup$#';

    const S_AUDIO_NATURE_WIND = '#$nature_wind$#';
    const S_AUDIO_NATURE_THUNDER = '#$nature_thunder$#';
    const S_AUDIO_NATURE_JUNGLE = '#$nature_jungle$#';
    const S_AUDIO_NATURE_RAIN = '#$nature_rain$#';
    const S_AUDIO_NATURE_FOREST = '#$nature_forest$#';
    const S_AUDIO_NATURE_SEA = '#$nature_sea$#';
    const S_AUDIO_NATURE_FIRE = '#$nature_fire$#';
    const S_AUDIO_NATURE_STREAM = '#$nature_stream$#';

    /**
     * Получение корректно составленного текста, в котором все ключи заменены на соответствующие звуки.
     *
     * @param array|null $sounds Пользовательские звуки.
     * @param string $text Исходный текст.
     * @return string
     * @api
     * @throws Exception
     */
    public function getSounds(?array $sounds, string $text): string
    {
        if ($this->isUsedStandardSound) {
            $sounds = array_merge($this->standardSounds, $sounds);
        }
        if ($sounds && is_array($sounds)) {
            foreach ($sounds as $sound) {
                if (is_array($sound)) {
                    if (isset($sound['sounds'], $sound['key'])) {
                        $sText = Text::getText($sound['sounds']);
                        /**
                         * Не стоит так делать, так как нужно время, пока Vk обработает звуковую дорожку.
                         * Лучше загружать звуки через консоль администратора!
                         * @see (https://vk.ru/dev/marusia_skill_docs10) Смотри тут
                         */
                        if (is_file($sText) || Text::isUrl($sText)) {
                            $sModel = new SoundTokens();
                            $sModel->type = SoundTokens::T_MARUSIA;
                            $sModel->path = $sText;
                            $sText = "<speaker audio_vk_id=\"{$sModel->getToken()}\">";
                        }

                        if ($sText) {
                            $text = $this->replaceSound($sound['key'], $sText, $text);
                        }
                    }
                }
            }
        }
        return $text;
    }

    /**
     * Замена ключей в тексте на соответствующие им звуки.
     *
     * @param string $key Ключ для поиска.
     * @param string|array $value Звук или массив звуков.
     * @param string $text Обрабатываемый текст.
     * @return string
     * @api
     */
    public function replaceSound(string $key, $value, string $text): string
    {
        return str_replace($key, Text::getText($value), $text);
    }

    /**
     * Удаление любых звуков и эффектов из текста.
     *
     * @param string $text Обрабатываемый текст.
     * @return string
     * @api
     */
    public static function removeSound(string $text): string
    {
        return preg_replace("/(<speaker audio=\"([^\"]+)\">)|(<speaker audio_vk_id=\"([^\"]+)\">)/ium", '', $text);
    }
}
