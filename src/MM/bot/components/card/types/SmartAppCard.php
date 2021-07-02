<?php

namespace MM\bot\components\card\types;

use MM\bot\components\button\Buttons;
use MM\bot\components\image\Image;

/**
 * Класс отвечающий за отображение карточки в Сбер SmartApp
 * Class SmartAppCard
 * @package bot\components\button\types
 */
class SmartAppCard extends TemplateCardTypes
{
    /**
     * Получение элементов для карточки
     *
     * @param Image $image Объект с картинкой
     * @param bool $isOne Получить результат для 1 карточки
     * @return array
     * @private
     */
    protected function getCardItem(Image $image, bool $isOne = false): array
    {
        if ($isOne) {
            $res = [];
            if ($image->imageDir) {
                $res[] = [
                    "type" => "image_cell_view",
                    "content" => [
                        "url" => $image->imageDir
                    ]
                ];
            }
            if ($image->title) {
                $res[] = [
                    "type" => "text_cell_view",
                    "paddings" => [
                        "top" => "6x",
                        "left" => "8x",
                        "right" => "8x"
                    ],
                    "content" => [
                        "text" => $image->title,
                        "typeface" => $image->params['titleTypeface'] ?? "title1",
                        "text_color" => $image->params['titleText_color'] ?? "default"
                    ]
                ];
            }
            if ($image->desc) {
                $res[] = [
                    "type" => "text_cell_view",
                    "paddings" => [
                        "top" => "4x",
                        "left" => "8x",
                        "right" => "8x"
                    ],
                    "content" => [
                        "text" => $image->desc,
                        "typeface" => $image->params['descTypeface'] ?? "footnote1",
                        "text_color" => $image->params['descText_color'] ?? "secondary"
                    ]
                ];
            }

            $button = $image->button->getButtons(Buttons::T_SMARTAPP_BUTTON_CARD);
            if ($button) {
                $res[] = [
                    "type" => "text_cell_view",
                    "paddings" => [
                        "top" => "12x",
                        "left" => "8x",
                        "right" => "8x"
                    ],
                    "content" => [
                        "actions" => [
                            $button
                        ],
                        "text" => $button['text'],
                        "typeface" => "button1",
                        "text_color" => "brand"
                    ]
                ];
            }
            return $res;
        }
        $cardItem = [
            'type' => 'left_right_cell_view',
            "paddings" => [
                "left" => "4x",
                "top" => "4x",
                "right" => "4x",
                "bottom" => "4x"
            ],
            'left' => [
                'type' => 'fast_answer_left_view',
                "icon_vertical_gravity" => "top",
                'icon_and_value' => [
                    'value' => [
                        'text' => $image->desc,
                        'typeface' => $image->params['descTypeface'] ?? 'body3',
                        'text_color' => $image->params['descText_color'] ?? 'default',
                        'max_lines' => $image->params['descMax_lines'] ?? 0,
                    ]
                ],
                'label' => [
                    'text' => $image->title,
                    'typeface' => $image->params['titleTypeface'] ?? 'headline2',
                    'text_color' => $image->params['titleText_color'] ?? 'default',
                    'max_lines' => $image->params['titleMax_lines'] ?? 0,
                ]
            ]
        ];
        if ($image->imageDir) {
            $cardItem['left']['icon_and_value']['icon'] = [
                'address' => [
                    'type' => 'url',
                    'url' => $image->imageDir
                ],
                'size' => [
                    "width" => "xlarge",
                    "height" => "xlarge"
                ],
                'margin' => [
                    "left" => "0x",
                    "right" => "6x"
                ]
            ];
        }
        $button = $image->button->getButtons(Buttons::T_SMARTAPP_BUTTON_CARD);
        if ($button) {
            $cardItem['actions'] = [$button];
        }
        return $cardItem;
    }

    public function getCard(bool $isOne): ?array
    {
        $countImage = count($this->images);
        if ($countImage) {
            if ($isOne) {
                $card = [
                    'type' => 'list_card'
                ];
                $card['sells'] = $this->getCardItem($this->images[0], true);
                return ['card' => $card];
            } else {
                $card = [
                    'type' => 'list_card',
                    'cells' => []
                ];
                if ($this->title) {
                    $card['cells'][] = [
                        "type" => "text_cell_view",
                        "paddings" => [
                            "top" => "4x",
                            "left" => "2x",
                            "right" => "2x"
                        ],
                        "content" => [
                            "text" => $this->title,
                            "typeface" => "title1",
                            "text_color" => "default"
                        ]
                    ];
                }
                foreach ($this->images as $image) {
                    $card['cells'][] = $this->getCardItem($image);
                }
                return ['card' => $card];
            }
        }
        return null;
    }
}
