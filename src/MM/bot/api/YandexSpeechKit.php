<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 26.03.2020
 * Time: 13:02
 */

namespace MM\bot\core\api;


use MM\bot\core\mmApp;

/**
 * Класс для преобразования текста в аудио файл
 * Преобразование осуществляется через сервис Yandex SpeechKit
 *
 * Class YandexSpeechKit
 * @package bot\core\api
 *
 * @property string $text: Текст, который нужно озвучить, в кодировке UTF-8.
 * Можно использовать только одно из полей text и ssml.
 * Для передачи слов-омографов используйте + перед ударной гласной. Например, гот+ов или def+ect.
 * Чтобы отметить паузу между словами, используйте -.
 * Ограничение на длину строки: 5000 символов.
 *
 * @property string $lang: Язык.
 * Допустимые значения:
 * ru-RU (по умолчанию) — русский язык;
 * en-US — английский язык;
 * tr-TR — турецкий язык.
 *
 * @property string $voice: Желаемый голос для синтеза речи из списка. Значение параметра по умолчанию: oksana.
 * @property string $emotion: Эмоциональная окраска голоса. Поддерживается только при выборе русского языка (ru-RU) и голосов jane или omazh.
 * Допустимые значения:
 * good — доброжелательный;
 * evil — злой;
 * neutral (по умолчанию) — нейтральный.
 *
 * @property int|float $speed: Скорость (темп) синтезированной речи. Для премиум-голосов временно не поддерживается.
 * Скорость речи задается дробным числом в диапазоне от 0.1 до 3.0. Где:
 * 3.0 — самый быстрый темп;
 * 1.0 (по умолчанию) — средняя скорость человеческой речи;
 * 0.1 — самый медленный темп.
 *
 * @property string $format: Формат синтезируемого аудио.
 * Допустимые значения:
 * lpcm — аудиофайл синтезируется в формате LPCM без WAV-заголовка. Характеристики аудио:
 * Дискретизация — 8, 16 или 48 кГц в зависимости от значения параметра sampleRateHertz.
 * Разрядность квантования — 16 бит.
 * Порядок байтов — обратный (little-endian).
 * Аудиоданные хранятся как знаковые числа (signed integer).
 * oggopus (по умолчанию) — данные в аудиофайле кодируются с помощью аудиокодека OPUS и упаковываются в контейнер OGG (OggOpus).
 *
 * @property string $sampleRateHertz: Частота дискретизации синтезируемого аудио.
 * Применяется, если значение format равно lpcm. Допустимые значения:
 * 48000 (по умолчанию) — частота дискретизации 48 кГц;
 * 16000 — частота дискретизации 16 кГц;
 * 8000 — частота дискретизации 8 кГц.
 *
 * @property string $folderId: Идентификатор каталога, к которому у вас есть доступ. Требуется для авторизации с пользовательским аккаунтом (см. ресурс UserAccount ). Не используйте это поле, если вы делаете запрос от имени сервисного аккаунта.
 * Максимальная длина строки в символах — 50.
 */
class YandexSpeechKit extends YandexRequest
{
    public const TTS_API_URL = 'https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize';

    public const E_GOOD = 'good';
    public const E_EVIL = 'evil';
    public const E_NEUTRAL = 'neutral';

    public const V_OKSANA = 'oksana';//ru
    public const V_JANE = 'jane';//ru
    public const V_OMAZH = 'omazh';//ru
    public const V_ZAHAR = 'zahar';//ru
    public const V_ERMIL = 'ermil';//ru
    public const V_SILAERKAN = 'silaerkan';//tr
    public const V_ERKANYAVAS = 'erkanyavas';//tr
    public const V_ALYSS = 'alyss';//en
    public const V_NICK = 'nick';//en
    public const V_ALENA = 'alena';//ru
    public const V_FILIPP = 'filipp';//ru

    public const L_RU = 'ru-RU';
    public const L_EN = 'en_EN';
    public const L_TR = 'tr-TR';

    public const F_LPCM = 'lpcm';
    public const F_OGGOPUS = 'oggopus';

    public $text;
    public $lang;
    public $voice;
    public $emotion;
    public $speed;
    public $format;
    public $sampleRateHertz;
    public $folderId;

    /**
     * YandexSpeechKit constructor.
     * @param string|null $oauth : Авторизационный токен для успешного получения tts
     */
    public function __construct(?string $oauth = null)
    {
        $this->lang = self::L_RU;
        $this->emotion = self::E_NEUTRAL;
        $this->speed = 1.0;
        $this->format = self::F_OGGOPUS;
        $this->folderId = null;
        if ($oauth === null) {
            $oauth = mmApp::$params['yandex_speech_kit_token'] ?? null;
        }
        parent::__construct($oauth);
    }

    /**
     * Инициализация параметров для отправки запроса
     */
    protected function initPost()
    {
        $this->request->post = [
            'text' => $this->text,
            'lang' => $this->lang,
            'voice' => $this->voice,
            'format' => $this->format
        ];
        if (!in_array($this->voice, [self::V_SILAERKAN, self::V_ERKANYAVAS, self::V_ALYSS, self::V_NICK])) {
            $this->request->post['emotion'] = $this->emotion;
        }
        if ($this->voice !== self::V_ALENA && $this->voice !== self::V_FILIPP) {
            if ($this->speed < 0.1 || $this->speed > 3.0) {
                $this->speed = 1.0;
            }
            $this->request->post['speed'] = (string)$this->speed;
        }
        if ($this->format == self::F_LPCM && $this->sampleRateHertz) {
            $this->request->post['sampleRateHertz'] = $this->sampleRateHertz;
        }
        if ($this->folderId) {
            $this->request->post['folderId'] = $this->folderId;
        }
    }

    /**
     * Получение голосового текста
     * Если синтез прошел успешно, в ответе будет бинарное содержимое аудиофайла.
     * Формат выходных данных зависит от значения параметра format
     *
     * @param null|string $text : Текст для преобразования
     * @return mixed
     * @see (https://cloud.yandex.ru/docs/speechkit/tts/request)
     */
    public function getTts(?string $text = null)
    {
        if ($text) {
            $this->text = $text;
        }
        $this->request->url = self::TTS_API_URL;
        $this->request->isConvertJson = false;
        $this->initPost();
        $query = $this->call();
        return $query;
    }
}
