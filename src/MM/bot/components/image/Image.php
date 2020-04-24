<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 8:59
 */

namespace MM\bot\components\image;


use MM\bot\components\button\Buttons;
use MM\bot\components\standard\Text;

/**
 * Class Image
 * @package bot\components\image
 *
 * @see Buttons
 * @property Buttons $button: Кнопки, обрабатывающие действия на нажатие изображения или непосредственно кнопок.
 * @property string $title: Название картинки
 * @property string $desc: Описание картинки
 * @property string $imageToken: Идентификатор картинки
 * @property string $imageDir: Расположение картинки в сети/директории
 * @property bool $isToken: True, если однозначно используется идентификатор/токен картинки. По умолчанию false
 */
class Image
{
    public $button;
    public $title;
    public $desc;
    public $imageToken;
    public $imageDir;
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
     * Инициализация значений для картинки
     *
     * @param string|null $image : Путь до картинки в сети/папке. Либо идентификатор картинки
     * @param string $title : Заголовок для картинки
     * @param string $desc : Описание для картинки
     * @param array|string|null $button : Возможные кнопки для картинки
     * @return bool
     */
    public function init(?string $image, $title, $desc = ' ', $button = null): bool
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
