<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\card;


use MM\bot\components\button\Buttons;
use MM\bot\components\card\types\AlisaCard;
use MM\bot\components\card\types\SmartAppCard;
use MM\bot\components\card\types\TelegramCard;
use MM\bot\components\card\types\TemplateCardTypes;
use MM\bot\components\card\types\ViberCard;
use MM\bot\components\card\types\VkCard;
use MM\bot\components\image\Image;
use MM\bot\core\mmApp;

/**
 * Отвечает за отображение определенной карточки, в зависимости от типа приложения.
 * Class Card
 * @package bot\components\card
 */
class Card
{
    /**
     * Заголовок для карточки.
     * @var string|null $title Заголовок для карточки.
     */
    public $title;
    /**
     * Описание карточки.
     * @var string|null $desc Описание карточки.
     */
    public $desc;
    /**
     * Массив с картинками или элементами карточки.
     * @var Image[]|null $images Массив с картинками или элементами карточки.
     * @see Image Смотри тут
     */
    public $images;
    /**
     * Кнопки для карточки.
     * @var Buttons $button Кнопки для карточки.
     * @see Buttons Смотри тут
     */
    public $button;
    /**
     * True, если в любом случае отобразить только 1 изображение.
     * @var bool $isOne True, если в любом случае отобразить только 1 изображение.
     */
    public $isOne;

    /**
     * Card constructor.
     */
    public function __construct()
    {
        $this->isOne = false;
        $this->button = new Buttons();
        $this->clear();
    }

    /**
     * Удалить все карточки с изображениями.
     * @api
     */
    public function clear()
    {
        $this->images = [];
    }

    /**
     * Вставить элемент в каточку|список.
     *
     * @param string|null $image Идентификатор или расположение картинки.
     * @param string $title Заголовок для картинки.
     * @param string $desc Описание для картинки.
     * @param array|null $button Кнопки, обрабатывающие команды при нажатии на элемент.
     * @api
     */
    public function add(?string $image, string $title, string $desc = ' ', $button = null): void
    {
        $img = new Image();
        if ($img->init($image, $title, $desc, $button)) {
            $this->images[] = $img;
        }
    }

    /**
     * Получить все элементы типа карточка.
     *
     * @param TemplateCardTypes|null $userCard Пользовательский класс для отображения каточки.
     * @return array
     * @api
     */
    public function getCards(?TemplateCardTypes $userCard = null): array
    {
        $card = null;
        switch (mmApp::$appType) {
            case T_ALISA:
                $card = new AlisaCard();
                break;

            case T_VK:
                $card = new VkCard();
                break;

            case T_TELEGRAM:
                $card = new TelegramCard();
                break;

            case T_VIBER:
                $card = new ViberCard();
                break;

            case T_MARUSIA:
                $card = null;
                break;

            case T_SMARTAPP:
                $card = new SmartAppCard();
                break;

            case T_USER_APP:
                $card = $userCard;
                break;
        }
        if ($card) {
            $card->images = $this->images;
            $card->button = $this->button;
            $card->title = $this->title;
            return $card->getCard($this->isOne);
        }
        return [];
    }

    /**
     * Возвращает json строку с данными о карточке.
     *
     * @return string
     * @api
     */
    public function getCardsJson(): string
    {
        $json = $this->getCards();
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }
}
