<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 05.03.2020
 * Time: 9:29
 */

namespace MM\bot\components\button;

use MM\bot\components\standard\Text;

/**
 * Class Button
 * @package bot\components\button
 *
 * Отображаемые кнопки при отправке сообщения пользователю
 * Тип кнопок(кнопка и сайджест) влияют только при отображении в Алисе.
 * В Vk и Telegram кнопки инициируются автоматически. Так как все кнопки с ссылками должны быть в виде сайджест кнопки.
 *
 * @property string $type: Тип кнопки
 * @property string $title: Текст на кнопке
 * @property string $url: Ссылка для перехода при нажатии кнопки
 * @property string|array $payload: Произвольные данные, отправляемые при нажатии кнопки
 * @property bool $hide: True, чтобы отображать кнопку как сайджест
 * @property array $options: Дополнительные параметры кнопки
 */
class Button
{
    public const B_LINK = false;
    public const B_BTN = true;

    public const VK_COLOR_PRIMARY = 'primary';
    public const VK_COLOR_SECONDARY = 'secondary';
    public const VK_COLOR_NEGATIVE = 'negative';
    public const VK_COLOR_POSITIVE = 'positive';

    public const VK_TYPE_TEXT = 'text';
    public const VK_TYPE_LINK = 'open_link';
    public const VK_TYPE_LOCATION = 'location';
    public const VK_TYPE_PAY = 'vkpay';
    public const VK_TYPE_APPS = 'open_app';

    public $type;
    public $title;
    public $url;
    public $payload;
    public $hide;
    public $options;

    /**
     * Button constructor.
     */
    public function __construct()
    {
        $this->type = null;
        $this->title = null;
        $this->url = null;
        $this->payload = [];
        $this->hide = self::B_LINK;
        $this->options = [];
    }

    /**
     * Инициализация кнопки
     *
     * @param string $title : Текст на кнопке
     * @param string|null $url : Ссылка для перехода, при нажатии кнопки
     * @param string|array|null $payload : Произвольные данные, отправляемые при нажатии кнопки
     * @param bool|null $hide : True, чтобы отображать кнопку как сайджест
     * @return bool
     */
    private function init(string $title, ?string $url, $payload, $hide): bool
    {
        if ($title || $title == '') {
            $this->title = (string)$title;
            if ($url && Text::isSayText(['http\:\/\/', 'https\:\/\/'], $url)) {
                if (Text::isSayText('utm_source', $url)) {
                    if (strpos($url, '?') !== false) {
                        $url .= '&';
                    } else {
                        $url .= '?';
                    }
                    $url .= 'utm_source=Yandex_Alisa&utm_medium=cpc&utm_campaign=phone';
                }
            } else {
                $url = null;
            }
            $this->url = $url;
            $this->payload = $payload;
            $this->hide = $hide;
            return true;
        }
        return false;
    }

    /**
     * Инициализация кнопки в виде сайджеста(ссылки под текстом)
     *
     * @param string $title : Текст на кнопке
     * @param string|null $url : Ссылка для перехода, при нажатии кнопки
     * @param string|array|null $payload : Произвольные данные, отправляемые при нажатии кнопки
     * @return bool
     */
    public function initLink($title, $url = '', $payload = null): bool
    {
        return $this->init($title, $url, $payload, self::B_LINK);
    }

    /**
     * Инициализация кнопки в виде кнопки
     *
     * @param string $title : Текст на кнопке
     * @param string|null $url : Ссылка для перехода, при нажатии кнопки
     * @param string|array|null $payload : Произвольные данные, отправляемые при нажатае кнопки
     * @return bool
     */
    public function initBtn($title, $url = '', $payload = null): bool
    {
        return $this->init($title, $url, $payload, self::B_BTN);
    }
}
