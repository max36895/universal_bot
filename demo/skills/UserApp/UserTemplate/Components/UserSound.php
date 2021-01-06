<?php

use MM\bot\api\YandexSpeechKit;
use MM\bot\components\sound\types\TemplateSoundTypes;
use MM\bot\components\standard\Text;

class UserSound extends TemplateSoundTypes
{
    /**
     * Возвращаем массив с воспроизводимыми звуками.
     * В случае если передается параметр text, то можно отправить запрос в Yandex SpeechKit, для преобразования текста в голос
     *
     * @param array|null $sounds Массив звуков
     * @param string $text Исходный текст
     * @return array
     */
    public function getSounds(?array $sounds, $text = ''): array
    {
        if ($sounds && is_array($sounds)) {
            foreach ($sounds as $sound) {
                if (is_array($sound)) {
                    if (isset($sound['sounds'], $sound['key'])) {
                        $sText = Text::getText($sound['sounds']);
                        /*
                         * Сохраняем данные в массив, либо отправляем данные через запрос
                         */
                        return [$sText];
                    }
                }
            }
        }
        /*
         * если есть необходимость для прочтения текста
         */
        if ($text) {
            $speechKit = new YandexSpeechKit();
            $content = $speechKit->getTts($text);
            if ($content) {
                /*
                * Сохраняем данные в массив, либо отправляем данные через запрос.
                 * п.с. В $content находится содержимое файла!
                */
            }
        }
        return [];
    }
}
