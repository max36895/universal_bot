<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\components\button\Button;
use MM\bot\components\button\Buttons;
use MM\bot\components\button\types\ViberButton;
use MM\bot\components\button\types\VkButton;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{

    const DEFAULT_URL = 'https://test.ru';

    /**
     * @var Buttons
     */
    protected $defaultButtons;

    protected function assertPreConditions(): void
    {
        $this->defaultButtons = new Buttons();
        \MM\bot\core\mmApp::$params['utm_text'] = '';
        for ($i = 0; $i < 3; $i++) {
            $index = (string)($i + 1);
            $this->defaultButtons->addBtn($index);
            $this->defaultButtons->addLink($index, self::DEFAULT_URL);
        }
    }

    public function testUtmText()
    {
        $button = new Button();
        \MM\bot\core\mmApp::$params['utm_text'] = null;
        $button->initBtn('btn', 'https://google.com');
        $this->assertEquals($button->url, 'https://google.com?utm_source=Yandex_Alisa&utm_medium=cpc&utm_campaign=phone');

        $button->initBtn('btn', 'https://google.com?utm_source=test');
        $this->assertEquals($button->url, 'https://google.com?utm_source=test');

        $button->initBtn('btn', 'https://google.com?data=test');
        $this->assertEquals($button->url, 'https://google.com?data=test&utm_source=Yandex_Alisa&utm_medium=cpc&utm_campaign=phone');

        \MM\bot\core\mmApp::$params['utm_text'] = 'my_utm_text';
        $button->initBtn('btn', 'https://google.com');
        $this->assertEquals($button->url, 'https://google.com?my_utm_text');
    }

    public function testGetBtnAlisa()
    {
        $alisaButtons = [
            [
                'title' => '1',
                'hide' => true
            ],
            [
                'title' => '1',
                'hide' => false,
                'url' => self::DEFAULT_URL
            ],
            [
                'title' => '2',
                'hide' => true
            ],
            [
                'title' => '2',
                'hide' => false,
                'url' => self::DEFAULT_URL
            ],
            [
                'title' => '3',
                'hide' => true
            ],
            [
                'title' => '3',
                'hide' => false,
                'url' => self::DEFAULT_URL
            ]
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_ALISA_BUTTONS), $alisaButtons);

        $this->defaultButtons->btns = [
            [
                'title' => 'btn',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $this->defaultButtons->links = [
            [
                'title' => 'link',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];

        $alisaButtons[] = [
            'title' => 'btn',
            'url' => self::DEFAULT_URL,
            'payload' => 'test',
            'hide' => true
        ];
        $alisaButtons[] = [
            'title' => 'link',
            'url' => self::DEFAULT_URL,
            'payload' => 'test',
            'hide' => false
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_ALISA_BUTTONS), $alisaButtons);
    }

    public function testGetBtnAlisaCard()
    {
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_ALISA_CARD_BUTTON), [
            'text' => '1'
        ]);
    }

    public function testGetBtnVk()
    {
        $vkButtons = [
            'one_time' => true,
            'buttons' => [
                [
                    'action' => [
                        'type' => Button::VK_TYPE_TEXT,
                        'label' => '1'
                    ]
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_LINK,
                        'link' => self::DEFAULT_URL,
                        'label' => '1'
                    ]
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_TEXT,
                        'label' => '2'
                    ]
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_LINK,
                        'link' => self::DEFAULT_URL,
                        'label' => '2'
                    ]
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_TEXT,
                        'label' => '3'
                    ]
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_LINK,
                        'link' => self::DEFAULT_URL,
                        'label' => '3'
                    ]
                ]
            ]
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VK_BUTTONS), $vkButtons);

        $this->defaultButtons->btns = [
            [
                'title' => 'btn',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $this->defaultButtons->links = [
            [
                'title' => 'link',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $vkButtons['buttons'][] = [
            'action' => [
                'type' => Button::VK_TYPE_LINK,
                'link' => self::DEFAULT_URL,
                'label' => 'btn',
                'payload' => 'test'
            ]
        ];
        $vkButtons['buttons'][] = [
            'action' => [
                'type' => Button::VK_TYPE_LINK,
                'link' => self::DEFAULT_URL,
                'label' => 'link',
                'payload' => 'test'
            ]
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VK_BUTTONS), $vkButtons);

        $this->defaultButtons->clear();
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VK_BUTTONS), ['one_time' => false, 'buttons' => []]);

    }

    public function testGetBtnVkGroup()
    {
        $vkButtons = [
            'one_time' => true,
            'buttons' => [
                [
                    [
                        'action' => [
                            'type' => Button::VK_TYPE_TEXT,
                            'label' => '1',
                            'payload' => '{}'
                        ]
                    ],
                    [
                        'action' => [
                            'type' => Button::VK_TYPE_LINK,
                            'link' => self::DEFAULT_URL,
                            'label' => '1',
                            'payload' => '{}'
                        ]
                    ],
                    [
                        'action' => [
                            'type' => Button::VK_TYPE_TEXT,
                            'label' => '2',
                            'payload' => '{}'
                        ]
                    ],
                    [
                        'action' => [
                            'type' => Button::VK_TYPE_LINK,
                            'link' => self::DEFAULT_URL,
                            'label' => '2',
                            'payload' => '{}'
                        ]
                    ],
                ],
                [
                    [
                        'action' => [
                            'type' => Button::VK_TYPE_TEXT,
                            'label' => '3',
                            'payload' => '{}'
                        ]
                    ],
                ],
                [
                    'action' => [
                        'type' => Button::VK_TYPE_LINK,
                        'link' => self::DEFAULT_URL,
                        'label' => '3'
                    ]
                ]
            ]
        ];
        $this->defaultButtons->clear();
        $this->defaultButtons->addBtn('1', null, '{}', [VkButton::GROUP_NAME => 0]);
        $this->defaultButtons->addLink('1', self::DEFAULT_URL, '{}', [VkButton::GROUP_NAME => 0]);
        $this->defaultButtons->addBtn('2', null, '{}', [VkButton::GROUP_NAME => 0]);
        $this->defaultButtons->addLink('2', self::DEFAULT_URL, '{}', [VkButton::GROUP_NAME => 0]);

        $this->defaultButtons->addBtn('3', null, '{}', [VkButton::GROUP_NAME => 1]);
        $this->defaultButtons->addLink('3', self::DEFAULT_URL);
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VK_BUTTONS), $vkButtons);

        $this->defaultButtons->btns = [
            [
                'title' => 'btn',
                'url' => self::DEFAULT_URL,
                'payload' => '{}',
                'options' => [
                    VkButton::GROUP_NAME => 1
                ]
            ]
        ];
        $this->defaultButtons->links = [
            [
                'title' => 'link',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];

        $vkButtons['buttons'][1][] =
            [
                'action' => [
                    'type' => Button::VK_TYPE_LINK,
                    'link' => self::DEFAULT_URL,
                    'label' => 'btn',
                    'payload' => '{}'
                ]
            ];
        $vkButtons['buttons'][] = [
            'action' => [
                'type' => Button::VK_TYPE_LINK,
                'link' => self::DEFAULT_URL,
                'label' => 'link',
                'payload' => 'test'
            ]
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VK_BUTTONS), $vkButtons);
    }

    public function testGetBtnViber()
    {
        $viberButtons = [
            'DefaultHeight' => true,
            'BgColor' => '#FFFFFF',
            'Buttons' => [
                [
                    'Text' => '1',
                    'ActionType' => ViberButton::T_REPLY,
                    'ActionBody' => '1'
                ],
                [
                    'Text' => '1',
                    'ActionType' => ViberButton::T_OPEN_URL,
                    'ActionBody' => self::DEFAULT_URL
                ],
                [
                    'Text' => '2',
                    'ActionType' => ViberButton::T_REPLY,
                    'ActionBody' => '2'
                ],
                [
                    'Text' => '2',
                    'ActionType' => ViberButton::T_OPEN_URL,
                    'ActionBody' => self::DEFAULT_URL
                ],
                [
                    'Text' => '3',
                    'ActionType' => ViberButton::T_REPLY,
                    'ActionBody' => '3'
                ],
                [
                    'Text' => '3',
                    'ActionType' => ViberButton::T_OPEN_URL,
                    'ActionBody' => self::DEFAULT_URL
                ],
            ]
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VIBER_BUTTONS), $viberButtons);

        $this->defaultButtons->btns = [
            [
                'title' => 'btn',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $this->defaultButtons->links = [
            [
                'title' => 'link',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $viberButtons['Buttons'][] = [
            'Text' => 'btn',
            'ActionType' => ViberButton::T_OPEN_URL,
            'ActionBody' => self::DEFAULT_URL
        ];
        $viberButtons['Buttons'][] = [
            'Text' => 'link',
            'ActionType' => ViberButton::T_OPEN_URL,
            'ActionBody' => self::DEFAULT_URL
        ];
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_VIBER_BUTTONS), $viberButtons);
    }

    public function testGetBtnTelegram()
    {
        $telegramButtons = [
            'keyboard' => [
                '1', '2', '3'
            ]
        ];

        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_TELEGRAM_BUTTONS), $telegramButtons);

        $this->defaultButtons->btns = [
            [
                'title' => 'btn',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];
        $this->defaultButtons->links = [
            [
                'title' => 'link',
                'url' => self::DEFAULT_URL,
                'payload' => 'test'
            ]
        ];

        $telegramButtons['inline_keyboard'] = [];
        $telegramButtons['inline_keyboard'][] = [
            'text' => 'btn',
            'url' => self::DEFAULT_URL,
            'callback_data' => 'test'
        ];
        $telegramButtons['inline_keyboard'][] = [
            'text' => 'link',
            'url' => self::DEFAULT_URL,
            'callback_data' => 'test'
        ];

        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_TELEGRAM_BUTTONS), $telegramButtons);

        $this->defaultButtons->clear();
        $this->assertEquals($this->defaultButtons->getButtons(Buttons::T_TELEGRAM_BUTTONS), ['remove_keyboard' => true]);
    }
}
