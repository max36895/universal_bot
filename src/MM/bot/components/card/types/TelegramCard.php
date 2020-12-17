<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\card\types;


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
     * @param bool $isOne True, если отобразить только 1 картинку. Не используется.
     * @return array
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
