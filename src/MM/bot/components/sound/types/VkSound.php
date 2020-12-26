<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\sound\types;

use MM\bot\components\standard\Text;
use MM\bot\api\YandexSpeechKit;
use MM\bot\models\SoundTokens;

/**
 * Класс отвечающий за отправку голосовых сообщений в ВКонтакте.
 * Class VkSound
 * @package bot\components\sound\types
 */
class VkSound extends TemplateSoundTypes
{
    /**
     * Возвращаем массив с воспроизводимыми звуками.
     * В случае если передается параметр text, то отправляется запрос в Yandex SpeechKit, для преобразования текста в голос.
     *
     * @param array|null $sounds Массив звуков.
     * @param string $text Исходный текст.
     * @return array
     * @api
     */
    public function getSounds(?array $sounds, string $text = ''): array
    {
        $data = [];
        if ($sounds && is_array($sounds)) {
            foreach ($sounds as $sound) {
                if (is_array($sound)) {
                    if (isset($sound['sounds'], $sound['key'])) {
                        $sText = Text::getText($sound['sounds']);
                        if (is_file($sText) || Text::isSayText(['http\:\/\/', 'https\:\/\/'], $sText)) {
                            $sModel = new SoundTokens();
                            $sModel->type = SoundTokens::T_VK;
                            $sModel->path = $sText;
                            $sText = $sModel->getToken();
                        }

                        if ($sText) {
                            $data[] = $sText;
                        }
                    }
                }
            }
        }
        if ($text) {
            $speechKit = new YandexSpeechKit();
            $content = $speechKit->getTts($text);
            $sText = null;
            if ($content) {
                $sModel = new SoundTokens();
                $sModel->type = SoundTokens::T_VK;
                $sModel->isAttachContent = true;
                $sModel->path = $content;
                $sText = $sModel->getToken();
            }
            if ($sText) {
                $data[] = $sText;
            }
        }
        return $data;
    }
}
