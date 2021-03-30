<?php

namespace MM\bot\components\sound\types;

use Exception;
use MM\bot\api\ViberRequest;
use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;

/**
 * Класс отвечающий за отправку голосовых сообщений в Viber.
 * Class ViberSound
 * @package bot\components\sound\types
 */
class ViberSound extends TemplateSoundTypes
{
    /**
     * Возвращаем массив с воспроизводимыми звуками.
     * В случае если передается параметр text, то отправляется запрос в Yandex SpeechKit, для преобразования текста в голос(не отправляется!).
     *
     * @param array|null $sounds Массив звуков.
     * @param string $text Исходный текст.
     * @return array
     * @throws Exception
     * @api
     */
    public function getSounds(?array $sounds, string $text = ''): array
    {
        if ($sounds && is_array($sounds)) {
            foreach ($sounds as $sound) {
                if (is_array($sound)) {
                    if (isset($sound['sounds'], $sound['key'])) {
                        $sText = Text::getText($sound['sounds']);
                        (new ViberRequest())->sendFile(mmApp::$params['user_id'], $sText);
                    }
                }
            }
        }
        /*
        if ($text) {
            $speechKit = new YandexSpeechKit();
            $content = $speechKit->getTts($text);
            if ($content) {
                (new ViberRequest())->sendFile(mmApp::$params['user_id'], $content);
            }
        }*/
        return [];
    }
}
