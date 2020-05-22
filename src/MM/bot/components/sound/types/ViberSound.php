<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 11.03.2020
 * Time: 9:26
 */

namespace MM\bot\components\sound\types;

use MM\bot\components\standard\Text;
use MM\bot\api\ViberRequest;
use MM\bot\core\mmApp;

/**
 * Class ViberSound
 * @package bot\components\sound\types
 */
class ViberSound extends TemplateSoundTypes
{
    /**
     * Возвращает массив с отображаемыми звуками.
     * В случае если передается параметр text, то отправляется запрос в Yandex SpeechKit, для преобразования текста в голос(не отправляется!)
     *
     * @param array $sounds : Массив звуков
     * @param string $text : Исходный текст
     * @return array
     */
    public function getSounds($sounds, $text = ''): array
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
