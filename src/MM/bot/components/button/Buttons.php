<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\button;

use MM\bot\components\button\types\AlisaButton;
use MM\bot\components\button\types\TelegramButton;
use MM\bot\components\button\types\TemplateButtonTypes;
use MM\bot\components\button\types\ViberButton;
use MM\bot\components\button\types\VkButton;


/**
 * Отвечает за отображение определенных кнопок, в зависимости от типа приложения.
 * Class Buttons
 * @package bot\components\button
 */
class Buttons
{
    public const T_ALISA_BUTTONS = 'alisa_btn';
    public const T_ALISA_CARD_BUTTON = 'alisa_card_btn';
    public const T_VK_BUTTONS = 'vk_btn';
    public const T_TELEGRAM_BUTTONS = 'telegram_btn';
    public const T_VIBER_BUTTONS = 'viber_btn';
    public const T_USER_APP_BUTTONS = 'user_app_btn';

    /**
     * Массив с различными кнопками.
     * @var Button[]|null $buttons Массив с различными кнопками.
     * @see Button Смотри тут
     */
    public $buttons;
    /**
     * Массив из кнопок вида кнопка.
     * @var array|string|null $btn
     *  - string Текст, отображаемый на кнопке.
     *  or
     *  - array
     *      - string title    Текст, отображаемый на кнопке.
     *      - string url      Ссылка, по которой перейдет пользователь после нажатия на кнопку.
     *      - string payload  Дополнительные параметры, передаваемые при нажатие на кнопку.
     */
    public $btn;
    /**
     * Массив из кнопок вида ссылка.
     * @var array|null $link
     *  - string Текст, отображаемый на кнопке.
     *  or
     *  - array
     *      - string title    Текст, отображаемый на кнопке.
     *      - string url      Ссылка, по которой перейдет пользователь после нажатия на кнопку.
     *      - string payload  Дополнительные параметры, передаваемые при нажатие на кнопку.
     */
    public $link;
    /**
     * @var string $type Тип кнопок(кнопка в Алисе, кнопка в карточке Алисы, кнопка в Vk, кнопка в Telegram).
     */
    public $type;

    /**
     * Buttons constructor.
     */
    public function __construct()
    {
        $this->clear();
        $this->type = self::T_ALISA_BUTTONS;
    }

    /**
     * Очистка всех кнопок.
     * @api
     */
    public function clear(): void
    {
        $this->buttons = [];
        $this->btn = [];
        $this->link = [];
    }

    /**
     * Вставить кнопку.
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода при нажатии на кнопку.
     * @param string|array|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @param bool|null $hide True, если отображать кнопку как сайджест.
     *
     * @return bool
     */
    protected function add($title, ?string $url, $payload, ?bool $hide): bool
    {
        $button = new Button();
        if ($hide === Button::B_LINK) {
            if ($button->initLink($title, $url, $payload) === false) {
                $button = null;
            }
        } else {
            if ($button->initBtn($title, $url, $payload) === false) {
                $button = null;
            }
        }
        if ($button) {
            $this->buttons[] = $button;
            return true;
        }
        return false;
    }

    /**
     * Добавить кнопку типа кнопка.
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода при нажатии на кнопку.
     * @param string|array|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @return bool
     * @api
     */
    public function addBtn($title, ?string $url = '', $payload = ''): bool
    {
        return $this->add($title, $url, $payload, Button::B_BTN);
    }

    /**
     * Добавить кнопку типа сайджест.
     *
     * @param string $title Текст на кнопке.
     * @param string|null $url Ссылка для перехода при нажатии на кнопку.
     * @param array|string|null $payload Произвольные данные, отправляемые при нажатии кнопки.
     * @return bool
     * @api
     */
    public function addLink($title, ?string $url = '', $payload = ''): bool
    {
        return $this->add($title, $url, $payload, Button::B_LINK);
    }

    /**
     * Дополнительная обработка второстепенных кнопок.
     * А именно обрабатываются массивы btn и link. После чего все значения вносятся в массив buttons.
     */
    protected function processing(): void
    {
        if (count($this->btn)) {
            if (is_array($this->btn)) {
                foreach ($this->btn as $btn) {
                    if (is_array($btn)) {
                        $this->addBtn($btn['title'] ?? null, $btn['url'] ?? '', $btn['payload'] ?? null);
                    } else {
                        $this->addBtn($btn);
                    }
                }
            } else {
                $this->addBtn((string)$this->btn);
            }
        }
        if (count($this->link)) {
            if (is_array($this->link)) {
                foreach ($this->link as $link) {
                    if (is_array($link)) {
                        $this->addLink($link['title'] ?? null, $link['url'] ?? '', $link['payload'] ?? null);
                    } else {
                        $this->addLink($link);
                    }
                }
            } else {
                $this->addLink((string)$this->link);
            }
        }
    }

    /**
     * Возвращает массив с кнопками для ответа пользователю.
     *
     * @param string|null $type Тип приложения.
     * @param TemplateButtonTypes|null $userButton Класс с пользовательскими кнопками.
     * @return array
     * @api
     */
    public function getButtons(?string $type = null, ?TemplateButtonTypes $userButton = null): array
    {
        $this->processing();
        if ($type === null) {
            $type = $this->type;
        }
        $button = null;
        switch ($type) {
            case self::T_ALISA_BUTTONS:
                $button = new AlisaButton();
                $button->isCard = false;
                break;

            case self::T_ALISA_CARD_BUTTON:
                $button = new AlisaButton();
                $button->isCard = true;
                break;

            case self::T_VK_BUTTONS:
                $button = new VkButton();
                break;

            case self::T_TELEGRAM_BUTTONS:
                $button = new TelegramButton();
                break;

            case self::T_VIBER_BUTTONS:
                $button = new ViberButton();
                break;

            case self::T_USER_APP_BUTTONS:
                $button = $userButton;
                break;

        }
        if ($button) {
            $button->buttons = $this->buttons;
            return $button->getButtons();
        }
        return [];
    }

    /**
     * Возвращает строку из json объекта кнопок.
     *
     * @param string|null $type Тип приложения.
     * @return string|null
     * @api
     */
    public function getButtonJson(?string $type = null): ?string
    {
        $btn = $this->getButtons($type);
        if (count($btn)) {
            return json_encode($btn, JSON_UNESCAPED_UNICODE);
        }
        return null;
    }
}
