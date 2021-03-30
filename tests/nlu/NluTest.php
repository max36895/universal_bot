<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\components\nlu\Nlu;
use PHPUnit\Framework\TestCase;

class NluTest extends TestCase
{
    /**
     * @var Nlu
     */
    protected $nlu;

    protected function assertPreConditions(): void
    {
        $this->nlu = new Nlu();
        $nluContent = [
            'thisUser' => [
                'username' => 'name',
                'first_name' => 'fn',
                'last_name' => 'ln'
            ],
            'entities' => [
                [
                    'type' => Nlu::T_GEO,
                    'tokens' => [
                        'start' => 0,
                        'end' => 1
                    ],
                    'value' => [
                        'city' => "city"
                    ]
                ],
                [
                    'type' => Nlu::T_NUMBER,
                    'tokens' => [
                        'start' => 0,
                        'end' => 1
                    ],
                    'value' => [
                        'integer' => 512
                    ]
                ],
                [
                    'type' => Nlu::T_FIO,
                    'tokens' => [
                        'start' => 0,
                        'end' => 1
                    ],
                    'value' => [
                        'first_name' => "fn"
                    ]
                ],
                [
                    'type' => Nlu::T_DATETIME,
                    'tokens' => [
                        'start' => 0,
                        'end' => 1
                    ],
                    'value' => [
                        'year' => 2020
                    ]
                ],
            ],
            'intents' => [
                'custom' => [
                    'slots' => [
                        'name' => [
                            'type' => "YANDEX.STRING",
                            'tokens' => [
                                'start' => 1,
                                'end' => 2
                            ],
                            'value' => "test"
                        ],
                        'action' => [
                            'type' => "YANDEX.STRING",
                            'tokens' => [
                                'start' => 2,
                                'end' => 4
                            ],
                            'value' => "спит"
                        ]
                    ]
                ]
            ]
        ];
        $this->nlu->setNlu($nluContent);
    }

    public function testFindPhone()
    {
        $this->assertTrue(Nlu::getPhone('123456')['status']);
        $this->assertTrue(Nlu::getPhone('12-34-56')['status']);
        $this->assertTrue(Nlu::getPhone('89999999999')['status']);
        $this->assertTrue(Nlu::getPhone('8(999)999-99-99')['status']);
        $this->assertFalse(Nlu::getPhone('512')['status']);
        $this->assertFalse(Nlu::getPhone('test')['status']);
    }

    public function testFindEMail()
    {
        $this->assertTrue(Nlu::getEMail('test@test.ru')['status']);
        $this->assertTrue(Nlu::getEMail('test@test.test')['status']);
        $this->assertTrue(Nlu::getEMail('test@yandex.ru')['status']);
        $this->assertTrue(Nlu::getEMail('test@google.com')['status']);
        $this->assertFalse(Nlu::getEMail('test')['status']);
    }

    public function testFindLink()
    {
        $this->assertTrue(Nlu::getLink('https://test.ru')['status']);
        $this->assertTrue(Nlu::getLink('https://test.test')['status']);
        $this->assertTrue(Nlu::getLink('http://test.ru')['status']);
        $this->assertTrue(Nlu::getLink('http://test.test')['status']);
    }

    public function testFindUserName()
    {
        $this->assertEquals($this->nlu->getUserName(), [
            'username' => 'name',
            'first_name' => 'fn',
            'last_name' => 'ln'
        ]);
    }

    public function testGetFio()
    {
        $this->assertTrue($this->nlu->getFio()['status']);
        $this->assertEquals($this->nlu->getFio()['result'], [['first_name' => 'fn']]);
    }


    public function testGetGeo()
    {
        $this->assertTrue($this->nlu->getGeo()['status']);
        $this->assertEquals($this->nlu->getGeo()['result'], [['city' => 'city']]);
    }

    public function testGetDateTime()
    {
        $this->assertTrue($this->nlu->getDateTime()['status']);
        $this->assertEquals($this->nlu->getDateTime()['result'], [['year' => 2020]]);
    }

    public function testGetNumber()
    {
        $this->assertTrue($this->nlu->getNumber()['status']);
        $this->assertEquals($this->nlu->getNumber()['result'], [['integer' => 512]]);
    }

    public function testGetIntent()
    {
        $this->assertEquals($this->nlu->getIntent('custom'), [
            'slots' => [
                'name' => [
                    'type' => "YANDEX.STRING",
                    'tokens' => [
                        'start' => 1,
                        'end' => 2
                    ],
                    'value' => "test"
                ],
                'action' => [
                    'type' => "YANDEX.STRING",
                    'tokens' => [
                        'start' => 2,
                        'end' => 4
                    ],
                    'value' => "спит"
                ]
            ]
        ]);
        $this->assertTrue($this->nlu->getIntent('test') === null);
    }
}
