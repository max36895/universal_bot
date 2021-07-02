<?php

namespace MM\bot\components\button\types;


use MM\bot\components\button\Button;
use MM\bot\components\standard\Text;

/**
 * Класс отвечающий за отображение кнопок в Алисе
 * Class AlisaButton
 * @package bot\components\button\types
 */
class AlisaButton extends TemplateButtonTypes
{
    /**
     * Использование кнопок для карточки
     * True, если нужно отобразить кнопку для карточки. По умолчанию false
     * @var bool $isCard
     */
    public $isCard;

    /**
     * AlisaButton constructor.
     */
    public function __construct()
    {
        $this->isCard = false;
    }

    /**
     * Отображаем кнопку
     *
     * @param Button $button Кнопка для отображения
     * @return array
     */
    protected function getButton(Button $button): ?array
    {
        $title = Text::resize($button->title, 64);
        if ($title) {
            if ($this->isCard) {
                $object = [
                    'text' => $title,
                ];
            } else {
                $object = [
                    'title' => $title,
                    'hide' => $button->hide
                ];
            }
            if ($button->payload) {
                $object['payload'] = $button->payload;
            }
            if ($button->url) {
                $object['url'] = Text::resize($button->url, 1024);
            }
            return $object;
        }
        return null;
    }

    /**
     * Получение массива с кнопками для ответа пользователю.
     *
     * @return array
     * [
     *  - string text: Текст на кнопке.
     *  - string payload: Произвольные данные, которые будут отправлены при нажатии на кнопку.
     *  - string url: Ссылка по которой будет произведен переход после нажатия на кнопку.
     * ]
     * @api
     */
    public function getButtons(): array
    {
        $objects = [];
        if ($this->isCard) {
            if (!empty($this->buttons)) {
                return $this->getButton($this->buttons[0]);
            }
        } else {
            foreach ($this->buttons as $button) {
                $object = $this->getButton($button);
                if ($object) {
                    $objects[] = $object;
                }
            }
        }
        return $objects;
    }
}
