<?php

namespace MM\bot\components\sound\types;

use Exception;
use MM\bot\components\standard\Text;
use MM\bot\models\SoundTokens;

/**
 * Класс отвечающий за воспроизведение звуков в Алисе.
 * Class AlisaSound
 * @package bot\components\sound\types
 */
class AlisaSound extends TemplateSoundTypes
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
                '<speaker audio="alice-sounds-game-win-1.opus">',
                '<speaker audio="alice-sounds-game-win-2.opus">',
                '<speaker audio="alice-sounds-game-win-3.opus">',
            ]
        ],
        [
            'key' => '#$game_loss$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-loss-1.opus">',
                '<speaker audio="alice-sounds-game-loss-2.opus">',
                '<speaker audio="alice-sounds-game-loss-3.opus">',
            ]
        ],
        [
            'key' => '#$game_boot$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-boot-1.opus">',
            ]
        ],
        [
            'key' => '#$game_coin$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-8-bit-coin-1.opus">',
                '<speaker audio="alice-sounds-game-8-bit-coin-2.opus">',
            ]
        ],
        [
            'key' => '#$game_ping$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-ping-1.opus">',
            ]
        ],
        [
            'key' => '#$game_fly$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-8-bit-flyby-1.opus">',
            ]
        ],
        [
            'key' => '#$game_gun$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-8-bit-machine-gun-1.opus">',
            ]
        ],
        [
            'key' => '#$game_phone$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-8-bit-phone-1.opus">',
            ]
        ],
        [
            'key' => '#$game_powerup$#',
            'sounds' => [
                '<speaker audio="alice-sounds-game-powerup-1.opus">',
                '<speaker audio="alice-sounds-game-powerup-2.opus">',
            ]
        ],

        [
            'key' => '#$nature_wind$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-wind-1.opus">',
                '<speaker audio="alice-sounds-nature-wind-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_thunder$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-thunder-1.opus">',
                '<speaker audio="alice-sounds-nature-thunder-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_jungle$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-jungle-1.opus">',
                '<speaker audio="alice-sounds-nature-jungle-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_rain$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-rain-1.opus">',
                '<speaker audio="alice-sounds-nature-rain-2.opus">',
            ]
        ],
        [
            'key' => '#$$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-forest-1.opus">',
                '<speaker audio="alice-sounds-nature-forest-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_sea$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-sea-1.opus">',
                '<speaker audio="alice-sounds-nature-sea-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_fire$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-fire-1.opus">',
                '<speaker audio="alice-sounds-nature-fire-2.opus">',
            ]
        ],
        [
            'key' => '#$nature_stream$#',
            'sounds' => [
                '<speaker audio="alice-sounds-nature-stream-1.opus">',
                '<speaker audio="alice-sounds-nature-stream-2.opus">',
            ]
        ],
        [
            'key' => '#$thing_chainsaw$#',
            'sounds' => [
                '<speaker audio="alice-sounds-things-chainsaw-1.opus">',
                '<speaker audio="alice-sounds-things-explosion-1.opus">',
                '<speaker audio="alice-sounds-things-water-3.opus">',
                '<speaker audio="alice-sounds-things-water-1.opus">',
                '<speaker audio="alice-sounds-things-water-2.opus">',
                '<speaker audio="alice-sounds-things-switch-1.opus">',
                '<speaker audio="alice-sounds-things-switch-2.opus">',
                '<speaker audio="alice-sounds-things-gun-1.opus">',
                '<speaker audio="alice-sounds-transport-ship-horn-1.opus">',
                '<speaker audio="alice-sounds-transport-ship-horn-2.opus">',
                '<speaker audio="alice-sounds-things-door-1.opus">',
                '<speaker audio="alice-sounds-things-door-2.opus">',
                '<speaker audio="alice-sounds-things-glass-2.opus">',
                '<speaker audio="alice-sounds-things-bell-1.opus">',
                '<speaker audio="alice-sounds-things-bell-2.opus">',
                '<speaker audio="alice-sounds-things-car-1.opus">',
                '<speaker audio="alice-sounds-things-car-2.opus">',
                '<speaker audio="alice-sounds-things-sword-2.opus">',
                '<speaker audio="alice-sounds-things-sword-1.opus">',
                '<speaker audio="alice-sounds-things-sword-3.opus">',
                '<speaker audio="alice-sounds-things-siren-1.opus">',
                '<speaker audio="alice-sounds-things-siren-2.opus">',
                '<speaker audio="alice-sounds-things-old-phone-1.opus">',
                '<speaker audio="alice-sounds-things-old-phone-2.opus">',
                '<speaker audio="alice-sounds-things-glass-1.opus">',
                '<speaker audio="alice-sounds-things-construction-2.opus">',
                '<speaker audio="alice-sounds-things-construction-1.opus">',
                '<speaker audio="alice-sounds-things-phone-1.opus">',
                '<speaker audio="alice-sounds-things-phone-2.opus">',
                '<speaker audio="alice-sounds-things-phone-3.opus">',
                '<speaker audio="alice-sounds-things-phone-4.opus">',
                '<speaker audio="alice-sounds-things-phone-5.opus">',
                '<speaker audio="alice-sounds-things-toilet-1.opus">',
                '<speaker audio="alice-sounds-things-cuckoo-clock-2.opus">',
                '<speaker audio="alice-sounds-things-cuckoo-clock-1.opus">',
            ]
        ],
        [
            'key' => '#$animals_all$#',
            'sounds' => [
                '<speaker audio="alice-sounds-animals-wolf-1.opus">',
                '<speaker audio="alice-sounds-animals-crow-1.opus">',
                '<speaker audio="alice-sounds-animals-crow-2.opus">',
                '<speaker audio="alice-sounds-animals-cow-1.opus">',
                '<speaker audio="alice-sounds-animals-cow-2.opus">',
                '<speaker audio="alice-sounds-animals-cow-3.opus">',
                '<speaker audio="alice-sounds-animals-cat-1.opus">',
                '<speaker audio="alice-sounds-animals-cat-2.opus">',
                '<speaker audio="alice-sounds-animals-cat-3.opus">',
                '<speaker audio="alice-sounds-animals-cat-4.opus">',
                '<speaker audio="alice-sounds-animals-cat-5.opus">',
                '<speaker audio="alice-sounds-animals-cuckoo-1.opus">',
                '<speaker audio="alice-sounds-animals-chicken-1.opus">',
                '<speaker audio="alice-sounds-animals-lion-1.opus">',
                '<speaker audio="alice-sounds-animals-lion-2.opus">',
                '<speaker audio="alice-sounds-animals-horse-1.opus">',
                '<speaker audio="alice-sounds-animals-horse-2.opus">',
                '<speaker audio="alice-sounds-animals-horse-galloping-1.opus">',
                '<speaker audio="alice-sounds-animals-horse-walking-1.opus">',
                '<speaker audio="alice-sounds-animals-frog-1.opus">',
                '<speaker audio="alice-sounds-animals-seagull-1.opus">',
                '<speaker audio="alice-sounds-animals-monkey-1.opus">',
                '<speaker audio="alice-sounds-animals-sheep-1.opus">',
                '<speaker audio="alice-sounds-animals-sheep-2.opus">',
                '<speaker audio="alice-sounds-animals-rooster-1.opus">',
                '<speaker audio="alice-sounds-animals-elephant-1.opus">',
                '<speaker audio="alice-sounds-animals-elephant-2.opus">',
                '<speaker audio="alice-sounds-animals-dog-1.opus">',
                '<speaker audio="alice-sounds-animals-dog-2.opus">',
                '<speaker audio="alice-sounds-animals-dog-3.opus">',
                '<speaker audio="alice-sounds-animals-dog-4.opus">',
                '<speaker audio="alice-sounds-animals-dog-5.opus">',
                '<speaker audio="alice-sounds-animals-owl-1.opus">',
                '<speaker audio="alice-sounds-animals-owl-2.opus">',
            ]
        ],
        [
            'key' => '#$human_all$#',
            'sounds' => [
                '<speaker audio="alice-sounds-human-cheer-1.opus">',
                '<speaker audio="alice-sounds-human-cheer-2.opus">',
                '<speaker audio="alice-sounds-human-kids-1.opus">',
                '<speaker audio="alice-sounds-human-walking-dead-1.opus">',
                '<speaker audio="alice-sounds-human-walking-dead-2.opus">',
                '<speaker audio="alice-sounds-human-walking-dead-3.opus">',
                '<speaker audio="alice-sounds-human-cough-1.opus">',
                '<speaker audio="alice-sounds-human-cough-2.opus">',
                '<speaker audio="alice-sounds-human-laugh-1.opus">',
                '<speaker audio="alice-sounds-human-laugh-2.opus">',
                '<speaker audio="alice-sounds-human-laugh-3.opus">',
                '<speaker audio="alice-sounds-human-laugh-4.opus">',
                '<speaker audio="alice-sounds-human-laugh-5.opus">',
                '<speaker audio="alice-sounds-human-crowd-1.opus">',
                '<speaker audio="alice-sounds-human-crowd-2.opus">',
                '<speaker audio="alice-sounds-human-crowd-3.opus">',
                '<speaker audio="alice-sounds-human-crowd-4.opus">',
                '<speaker audio="alice-sounds-human-crowd-5.opus">',
                '<speaker audio="alice-sounds-human-crowd-7.opus">',
                '<speaker audio="alice-sounds-human-crowd-6.opus">',
                '<speaker audio="alice-sounds-human-sneeze-1.opus">',
                '<speaker audio="alice-sounds-human-sneeze-2.opus">',
                '<speaker audio="alice-sounds-human-walking-room-1.opus">',
                '<speaker audio="alice-sounds-human-walking-snow-1.opus">',
            ]
        ],
        [
            'key' => '#$music_all$#',
            'sounds' => [
                '<speaker audio="alice-music-harp-1.opus">',
                '<speaker audio="alice-music-drums-1.opus">',
                '<speaker audio="alice-music-drums-2.opus">',
                '<speaker audio="alice-music-drums-3.opus">',
                '<speaker audio="alice-music-drum-loop-1.opus">',
                '<speaker audio="alice-music-drum-loop-2.opus">',
                '<speaker audio="alice-music-tambourine-80bpm-1.opus">',
                '<speaker audio="alice-music-tambourine-100bpm-1.opus">',
                '<speaker audio="alice-music-tambourine-120bpm-1.opus">',
                '<speaker audio="alice-music-bagpipes-1.opus">',
                '<speaker audio="alice-music-bagpipes-2.opus">',
                '<speaker audio="alice-music-guitar-c-1.opus">',
                '<speaker audio="alice-music-guitar-e-1.opus">',
                '<speaker audio="alice-music-guitar-g-1.opus">',
                '<speaker audio="alice-music-guitar-a-1.opus">',
                '<speaker audio="alice-music-gong-1.opus">',
                '<speaker audio="alice-music-gong-2.opus">',
                '<speaker audio="alice-music-horn-2.opus">',
                '<speaker audio="alice-music-violin-c-1.opus">',
                '<speaker audio="alice-music-violin-c-2.opus">',
                '<speaker audio="alice-music-violin-a-1.opus">',
                '<speaker audio="alice-music-violin-e-1.opus">',
                '<speaker audio="alice-music-violin-d-1.opus">',
                '<speaker audio="alice-music-violin-b-1.opus">',
                '<speaker audio="alice-music-violin-g-1.opus">',
                '<speaker audio="alice-music-violin-f-1.opus">',
                '<speaker audio="alice-music-horn-1.opus">',
                '<speaker audio="alice-music-piano-c-1.opus">',
                '<speaker audio="alice-music-piano-c-2.opus">',
                '<speaker audio="alice-music-piano-a-1.opus">',
                '<speaker audio="alice-music-piano-e-1.opus">',
                '<speaker audio="alice-music-piano-d-1.opus">',
                '<speaker audio="alice-music-piano-b-1.opus">',
                '<speaker audio="alice-music-piano-g-1.opus">',
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
     * Получение разметки для вставки паузы между словами.
     *
     * @param int|float $milliseconds Пауза в миллисекундах.
     * @return string
     * @see (https://yandex.ru/dev/dialogs/alice/doc/speech-tuning-docpage/) Смотри тут
     * @api
     */
    public static function getPause($milliseconds): string
    {
        return "sil <[{$milliseconds}]>";
    }

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
                         * Не стоит так делать, так как нужно время, пока Yandex обработает звуковую дорожку.
                         * Лучше загружать звуки через консоль администратора!
                         * @see (https://dialogs.yandex.ru/developer/skills/<skill_id>/resources/sounds) Смотри тут
                         */
                        if (is_file($sText) || Text::isUrl($sText)) {
                            $sModel = new SoundTokens();
                            $sModel->type = SoundTokens::T_ALISA;
                            $sModel->path = $sText;
                            $sText = "<speaker audio=\"{$sModel->getToken()}\">";
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
        return preg_replace("/(<speaker audio=\"([^\"]+)\">)|(<speaker effect=\"([^\"]+)\">)|(sil <\\[\\d+\\]>)/im", '', $text);
    }
}
