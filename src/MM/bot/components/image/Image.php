<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\image;


use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;

/**
 * Класс отвечает за обработку и корректное отображение изображения, в зависимости от типа приложения.
 * Class Image
 * @package bot\components\image
 */
class Image
{
    /**
     * Кнопки, обрабатывающие действия на нажатие на изображение или кнопоку (Зависит от типа приложения).
     * @var Buttons $button
     * @see Buttons Смотри тут
     */
    public $button;
    /**
     * Название картинки.
     * @var string $title
     */
    public $title;
    /**
     * Описание для картинки.
     * @var string $desc
     */
    public $desc;
    /**
     * Идентификатор картинки.
     * @var string|null $imageToken
     */
    public $imageToken;
    /**
     * Расположение картинки в сети/директории.
     * @var string|null $imageDir
     */
    public $imageDir;
    /**
     * True, если однозначно используется идентификатор/токен картинки. По умолчанию false.
     * @var bool $isToken
     */
    public $isToken;

    /**
     * Image constructor.
     */
    public function __construct()
    {
        $this->button = new Buttons();
        $this->title = '';
        $this->desc = '';
        $this->imageToken = null;
        $this->imageDir = null;
        $this->isToken = false;
    }

    /**
     * Инициализация картинки.
     *
     * @param string|null $image Путь до картинки в сети/папке. Либо идентификатор картинки.
     * @param string $title Заголовок для картинки.
     * @param string $desc Описание для картинки.
     * @param array|string|null $button Возможные кнопки для картинки.
     * @return bool
     * @api
     */
    public function init(?string $image, string $title, string $desc = ' ', $button = null): bool
    {
        if ($this->isToken) {
            $this->imageToken = $image;
        } else {
            if ($image && (Text::isSayText(['http\:\/\/', 'https\:\/\/'], $image) || is_file($image))) {
                $this->imageDir = $image;
                $this->imageToken = null;
            } else {
                $this->imageToken = $image;
            }
        }
        if ($title) {
            $this->title = $title;
            if (!$desc) {
                $desc = ' ';
            }
            $this->desc = $desc;
            if ($button) {
                if (is_string($button)) {
                    $this->button->addBtn($button);
                } else {
                    $title = ($button['text'] ?? ($button[0] ?? null));
                    $url = ($button['link'] ?? ($button[1] ?? null));
                    $payload = ($button['payload'] ?? ($button[2] ?? null));
                    $this->button->addBtn($title, $url, $payload);
                }
            }
            return true;
        }
        return false;
    }
}
