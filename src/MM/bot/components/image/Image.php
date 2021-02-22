<?php

namespace MM\bot\components\image;


use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;

/**
 * Класс отвечающий за обработку и корректное отображение изображения, в зависимости от типа приложения.
 * Class Image
 * @package bot\components\image
 */
class Image
{
    /**
     * Кнопки, обрабатывающие действия на нажатие на изображение или кнопку (Зависит от типа приложения).
     * @var Buttons $button
     * @see Buttons Смотри тут
     */
    public $button;
    /**
     * Название изображения.
     * @var string $title
     */
    public $title;
    /**
     * Описание для изображения.
     * @var string $desc
     */
    public $desc;
    /**
     * Идентификатор изображения.
     * @var string|null $imageToken
     */
    public $imageToken;
    /**
     * Расположение изображения в сети/директории.
     * @var string|null $imageDir
     */
    public $imageDir;
    /**
     * True, если однозначно используется идентификатор/токен изображения. По умолчанию false.
     * @var bool $isToken
     */
    public $isToken;

    /**
     * Дополнительные параметры для изображения.
     * [
     *  string topTypeface Стиль верхнего текста
     *  string topText_color Цвет верхнего текста
     *  array topMargins Отступы верхнего текста
     *      [
     *          string left Размер отступа.
     *          string top Размер отступа.
     *          string right Размер отступа.
     *          string bottom Размер отступа.
     *      ]
     *  int topMax_lines Максимальное количество строк верхнего текста
     *  string bottomTypeface Стиль нижнего текста
     *  string bottomText_color Цвет нижнего текста
     *  array bottomMargins Отступы нижнего текста
     *      [
     *          string left Размер отступа.
     *          string top Размер отступа.
     *          string right Размер отступа.
     *          string bottom Размер отступа.
     *      ]
     *  int bottomMax_lines Максимальное количество строк нижнего текста
     * ]
     * @var array $params
     */
    public $params;

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
        $this->params = [];
    }

    /**
     * Инициализация изображения.
     *
     * @param string|null $image Путь до изображения в сети/папке. Либо идентификатор изображения.
     * @param string $title Заголовок для изображения.
     * @param string $desc Описание для изображения.
     * @param array|string|null $button Возможные кнопки для изображения.
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
