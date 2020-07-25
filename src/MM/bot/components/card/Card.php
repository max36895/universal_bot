<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 05.03.2020
 * Time: 15:09
 */

namespace MM\bot\components\card;


use MM\bot\components\button\Buttons;
use MM\bot\components\card\types\AlisaCard;
use MM\bot\components\card\types\TelegramCard;
use MM\bot\components\card\types\TemplateCardTypes;
use MM\bot\components\card\types\ViberCard;
use MM\bot\components\card\types\VkCard;
use MM\bot\components\image\Image;
use MM\bot\core\mmApp;

/**
 * Class Card
 * @package bot\components\card
 *
 * @see Image
 * @property Image[] $images: Массив с картинками или элементами карточки
 * @see Buttons
 * @property Buttons $button: Кнопки для карточки
 * @property string $title: Заголовок для карточки
 * @property string $desc: Описание карточки
 * @property bool $isOne: True, если в любом случае отобразить только 1 изображение.
 */
class Card
{
    public $title;
    public $desc;
    public $images;
    public $button;

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
     * Удалить все карточки с изображениями
     */
    public function clear()
    {
        $this->images = [];
    }

    /**
     * Вставить элемент в каточку|список
     *
     * @param string|null $image : Идентификатор или расположение картинки
     * @param string $title : Заголовок для картинки
     * @param string $desc : Описание для картинки
     * @param array|null $button : Кнопки, обрабатывающие команды при нажатии на элемент
     */
    public function add(?string $image, $title, $desc = ' ', $button = null): void
    {
        $img = new Image();
        if ($img->init($image, $title, $desc, $button)) {
            $this->images[] = $img;
        }
    }

    /**
     * Получить все элементы типа карточка
     *
     * @param TemplateCardTypes|null $userCard : Пользовательский класс для отображения каточки
     * @return array
     */
    public function getCards($userCard = null): array
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
     * Возвращает json строку с данными о карточке
     *
     * @return string
     */
    public function getCardsJson(): string
    {
        $json = $this->getCards();
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }
}
