<?php

namespace MM\bot\components\card\types;


use Exception;
use MM\bot\api\TelegramRequest;
use MM\bot\core\mmApp;
use MM\bot\models\ImageTokens;

/**
 * Класс отвечающий за отображение карточки в Телеграме.
 * Class TelegramCard
 * @package bot\components\card\types
 */
class TelegramCard extends TemplateCardTypes
{
    /**
     * Получение карточки для отображения пользователю.
     *
     * todo подумать над корректным отображением.
     * @param bool $isOne True, если нужно отобразить только 1 элемент. Не используется.
     * @return array
     * @throws Exception
     * @api
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $options = [];
        foreach ($this->images as $image) {
            if (!$image->imageToken) {
                if ($image->imageDir) {
                    $mImage = new ImageTokens();
                    $mImage->type = ImageTokens::T_TELEGRAM;
                    $mImage->caption = $image->desc;
                    $mImage->path = $image->imageDir;
                    $image->imageToken = $mImage->getToken();
                }
            } else {
                (new TelegramRequest())->sendPhoto(mmApp::$params['user_id'], $image->imageToken, $image->desc);
            }
            $options[] = $image->title;
        }
        if (count($options) > 1) {
            $object = [
                'question' => $this->title,
                'options' => $options
            ];
        }
        return $object;
    }
}
