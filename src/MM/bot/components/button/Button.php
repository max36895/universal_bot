<?php

namespace MM\bot\components\button;

use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;

/**
 * Class Button
 * @package bot\components\button
 *
 * Класс для кнопки, которая будет отображаться пользователю
 * Тип кнопки(кнопка и сайджест) влияют только при отображении в навыках.
 * В Vk и Telegram кнопка инициируются автоматически. Так как все кнопки с ссылками должны быть в виде сайджест кнопки.
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

    /**
     * Тип кнопки.
     * @var string|null $type
     */
    public $type;
    /**
     * Текст на кнопке.
     * @var string|null $title
     */
    public $title;
    /**
     * Ссылка для перехода при нажатии кнопки.
     * @var string|null $url
     */
    public $url;
    /**
     * Произвольные данные, отправляемые при нажатии кнопки.
     * @var string|array $payload
     */
    public $payload;
    /**
     * True, чтобы отображать кнопку как сайджест.
     * @var bool $hide
     */
    public $hide;
    /**
     * Дополнительные параметры для кнопки.
     * [
     *  string _group: Задается в том случае, если нужно объединить кнопку в группу.
     *  Дополнительные опции для кнопки.
     * ]
     * @var array $options
     */
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
     * Инициализация кнопки.
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода, при нажатии кнопки.
     * @param string|array|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @param bool|null $hide True, чтобы отображать кнопку как сайджест.
     * @param array $options Дополнительные параметры для кнопки.
     * @return bool
     * @see Button::options Описание опции options
     */
    private function init(string $title, ?string $url, $payload, bool $hide, array $options = []): bool
    {
        if ($title || $title === '') {
            $this->title = (string)$title;
            if ($url && Text::isSayText('((http|s:\/\/)[^( |\n)]+)', $url, true)) {
                if (mmApp::$params['utm_text'] === null) {
                    if (!Text::isSayText('utm_source', $url)) {
                        if (strpos($url, '?') !== false) {
                            $url .= '&';
                        } else {
                            $url .= '?';
                        }
                        $url .= 'utm_source=Yandex_Alisa&utm_medium=cpc&utm_campaign=phone';
                    }
                } elseif (mmApp::$params['utm_text']) {
                    if (strpos($url, '?') !== false) {
                        $url .= '&';
                    } else {
                        $url .= '?';
                    }
                    $url .= mmApp::$params['utm_text'];
                }
            } else {
                $url = null;
            }
            $this->url = $url;
            $this->payload = $payload;
            $this->hide = $hide;
            $this->options = $options;
            return true;
        }
        return false;
    }

    /**
     * Инициализация кнопки в виде сайджеста(ссылки под текстом).
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода, при нажатии кнопки.
     * @param string|array|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @param array $options Дополнительные параметры для кнопки
     * @return bool
     * @see Button::options Описание опции options
     * @api
     */
    public function initLink(string $title, ?string $url = '', $payload = null, array $options = []): bool
    {
        return $this->init($title, $url, $payload, self::B_LINK, $options);
    }

    /**
     * Инициализация кнопки в виде кнопки.
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода, при нажатии кнопки.
     * @param string|array|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @param array $options Дополнительные параметры для кнопки
     * @return bool
     * @see Button::options Описание опции options
     * @api
     */
    public function initBtn(string $title, ?string $url = '', $payload = null, array $options = []): bool
    {
        return $this->init($title, $url, $payload, self::B_BTN, $options);
    }
}
