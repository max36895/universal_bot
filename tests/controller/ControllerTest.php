<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';
require_once __DIR__ . '/MyController.php';

use MM\bot\core\mmApp;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @var MyController
     */
    protected $uController;
    protected $elements;

    protected function setUp()
    {
        $this->uController = new MyController();
    }


    public function testDefaultIntents()
    {
        $this->assertEquals($this->uController->testIntents(), mmApp::$params['intents']);
        $this->assertTrue($this->uController->testIntent('привет') === 'welcome');
        $this->assertTrue($this->uController->testIntent('помощь') === 'help');
        $this->assertTrue($this->uController->testIntent('test') === null);
        $this->assertTrue($this->uController->testIntent('start') === null);
        $this->assertTrue($this->uController->testIntent('go') === null);
        $this->assertTrue($this->uController->testIntent('by') === null);
    }

    public function testNullIntents()
    {
        mmApp::$params['intents'] = null;
        $this->assertEquals($this->uController->testIntents(), []);
        $this->assertTrue($this->uController->testIntent('test') === null);
        $this->assertTrue($this->uController->testIntent('start') === null);
        $this->assertTrue($this->uController->testIntent('go') === null);
        $this->assertTrue($this->uController->testIntent('by') === null);
    }

    public function testUserIntents()
    {
        $intents = [
            [
                'name' => 'start',
                'slots' => [
                    'start',
                    'go'
                ]
            ],
            [
                'name' => 'by',
                'slots' => [
                    'by'
                ],
                'is_pattern' => true
            ]
        ];
        mmApp::$params['intents'] = $intents;
        $this->assertEquals($this->uController->testIntents(), $intents);
        $this->assertTrue($this->uController->testIntent('test') === null);
        $this->assertTrue($this->uController->testIntent('start') === 'start');
        $this->assertTrue($this->uController->testIntent('go') === 'start');
        $this->assertTrue($this->uController->testIntent('by') === 'by');
        $this->assertTrue($this->uController->testIntent('bye') === 'by');
    }
}