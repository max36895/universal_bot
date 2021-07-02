<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\components\card\types\AlisaCard;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{

    const URL = 'https://test.ru';

    /**
     * @var \MM\bot\components\card\Card
     */
    protected $defaultCard;

    protected function assertPreConditions(): void
    {
        $this->defaultCard = new \MM\bot\components\card\Card();
        \MM\bot\core\mmApp::$params['utm_text'] = '';
        $this->defaultCard->title = 'title';
        $this->defaultCard->desc = 'desc';
        for ($i = 0; $i < 3; $i++) {
            $index = (string)($i + 1);
            $this->defaultCard->add('36895', $index, "запись: {$index}");
        }
    }


    public function testGetAlisaCard()
    {
        $alisaCard = [
            'type' => AlisaCard::ALISA_CARD_ITEMS_LIST,
            'header' => [
                'text' => 'title'
            ],
            'items' => [
                [
                    'title' => '1',
                    'description' => 'запись: 1',
                    'image_id' => '36895'
                ],
                [
                    'title' => '2',
                    'description' => 'запись: 2',
                    'image_id' => '36895'
                ],
                [
                    'title' => '3',
                    'description' => 'запись: 3',
                    'image_id' => '36895'
                ],
            ]
        ];
        \MM\bot\core\mmApp::$appType = T_ALISA;
        $this->assertEquals($this->defaultCard->getCards(), $alisaCard);

        $this->defaultCard->button->addBtn('1', self::URL);
        $alisaCard['footer'] = [
            'text' => '1',
            'button' => [
                'text' => '1',
                'url' => self::URL
            ]
        ];
        $this->assertEquals($this->defaultCard->getCards(), $alisaCard);

        $this->defaultCard->isOne = true;

        $alisaCardOne = [
            'type' => AlisaCard::ALISA_CARD_BIG_IMAGE,
            'image_id' => '36895',
            'title' => '1',
            'description' => 'запись: 1',
            'button' => [
                'text' => '1',
                'url' => self::URL
            ]
        ];
        $this->assertEquals($this->defaultCard->getCards(), $alisaCardOne);

        $this->defaultCard->button = new \MM\bot\components\button\Buttons();
        unset($alisaCardOne['button']);
        $this->assertEquals($this->defaultCard->getCards(), $alisaCardOne);


        $this->defaultCard->clear();
        $this->defaultCard->isOne = false;
        $this->defaultCard->add('36895', 'Запись 1', 'Описание 1', 'Кнопка');
        $this->defaultCard->add('36895', 'Запись 2', 'Описание 2', ['title' => 'Кнопка', 'url' => self::URL]);
        $this->defaultCard->add('36895', 'Запись 3', 'Описание 3', ['title' => 'Кнопка', 'payload' => ['text' => 'text']]);
        $alisaCardButton = [
            'type' => AlisaCard::ALISA_CARD_ITEMS_LIST,
            'header' => [
                'text' => 'title'
            ],
            'items' => [
                [
                    'title' => 'Запись 1',
                    'description' => 'Описание 1',
                    'image_id' => '36895',
                    'button' => [
                        'text' => 'Кнопка'
                    ]
                ],
                [
                    'title' => 'Запись 2',
                    'description' => 'Описание 2',
                    'image_id' => '36895',
                    'button' => [
                        'text' => 'Кнопка',
                        'url' => self::URL
                    ]
                ],
                [
                    'title' => 'Запись 3',
                    'description' => 'Описание 3',
                    'image_id' => '36895',
                    'button' => [
                        'text' => 'Кнопка',
                        'payload' => [
                            'text' => 'text'
                        ]
                    ]
                ],
            ]
        ];
        $this->assertEquals($this->defaultCard->getCards(), $alisaCardButton);
    }

    public function testGetAlisaGallery()
    {
        $this->defaultCard->isUsedGallery = true;
        $alisaGallery = [
            'type' => 'ImageGallery',
            'items' => [
                [
                    'title' => '1',
                    'image_id' => '36895'
                ],
                [
                    'title' => '2',
                    'image_id' => '36895'
                ],
                [
                    'title' => '3',
                    'image_id' => '36895'
                ]
            ]
        ];
        $this->assertEquals($this->defaultCard->getCards(), $alisaGallery);
        $this->defaultCard->isUsedGallery = false;
    }

    public function testGetViberCard()
    {
        $viberCard = [
            [
                'Columns' => 3,
                'Rows' => 6,
                'Image' => '36895'
            ],
            [
                'Columns' => 3,
                'Rows' => 6,
                'Image' => '36895'
            ],
            [
                'Columns' => 3,
                'Rows' => 6,
                'Image' => '36895'
            ]
        ];
        \MM\bot\core\mmApp::$appType = T_VIBER;
        $this->assertEquals($this->defaultCard->getCards(), $viberCard);

        $this->defaultCard->isOne = true;
        $viberCard[0]['Columns'] = 1;
        $this->assertEquals($this->defaultCard->getCards(), $viberCard[0]);

        $viberCard[0]['Text'] = '<font color=#000><b>1</b></font><font color=#000>запись: 1</font>';
        $viberCard[0]['ActionType'] = \MM\bot\components\button\types\ViberButton::T_REPLY;
        $viberCard[0]['ActionBody'] = '1';
        $buttons = new \MM\bot\components\button\Buttons();
        $buttons->addBtn('1');
        $this->defaultCard->images[0]->button = $buttons;
        $this->assertEquals($this->defaultCard->getCards(), $viberCard[0]);

        $this->defaultCard->isOne = false;
        $viberCard[0]['Columns'] = 3;
        $this->assertEquals($this->defaultCard->getCards(), $viberCard);
    }

    public function testGetVkCard()
    {
        $vkCard = [
            'type' => 'carousel',
            'elements' => [
                [
                    'title' => '1',
                    'description' => 'запись: 1',
                    'photo_id' => '36895',
                    'buttons' => [
                        [
                            'action' => [
                                'type' => \MM\bot\components\button\Button::VK_TYPE_TEXT,
                                'label' => '1'
                            ]
                        ]
                    ],
                    'action' => [
                        'type' => 'open_photo'
                    ]
                ]
            ]
        ];
        \MM\bot\core\mmApp::$appType = T_VK;
        $this->assertEquals($this->defaultCard->getCards(), []);

        $this->defaultCard->isOne = true;
        $this->assertEquals($this->defaultCard->getCards(), ['36895']);

        $this->defaultCard->isOne = false;
        $buttons = new \MM\bot\components\button\Buttons();
        $buttons->addBtn('1');
        $this->defaultCard->images[0]->button = $buttons;
        $this->assertEquals($this->defaultCard->getCards(), $vkCard);
    }
}
