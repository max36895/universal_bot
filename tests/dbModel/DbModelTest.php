<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\models\UsersData;
use PHPUnit\Framework\TestCase;

class DbModelTest extends TestCase
{

    protected $data;
    /**
     * @var UsersData
     */
    protected $userData;

    protected function assertPreConditions(): void
    {
        $this->userData = new UsersData();
        \MM\bot\core\mmApp::$params['utm_text'] = '';
        \MM\bot\core\mmApp::$config['json'] = __DIR__;
        $this->data = [
            'userId1' => [
                'userId' => 'userId1',
                'meta' => 'user meta 1',
                'data' => [
                    'name' => 'user 1'
                ]
            ],
            'userId2' => [
                'userId' => 'userId2',
                'meta' => 'user meta 2',
                'data' => [
                    'name' => 'user 2'
                ]
            ],
            'userId3' => [
                'userId' => 'userId3',
                'meta' => 'user meta 3',
                'data' => [
                    'name' => 'user 3'
                ]
            ],
            'userId13' => [
                'userId' => 'userId3',
                'meta' => 'user meta 1',
                'data' => [
                    'name' => 'user 3'
                ]
            ]
        ];
        \MM\bot\core\mmApp::saveJson('UsersData.json', $this->data);
    }

    /**
     * Вызывается после прохождения последнего теста
     */
    public static function tearDownAfterClass(): void
    {
        unlink(__DIR__ . '/UsersData.json');
    }

    public function testWhereString()
    {
        $query = '`userId`="userId1"';
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 1);
        $this->assertEquals($uData[0], $this->data['userId1']);

        $query = '`userId`="userId3" AND `meta`="user meta 1"';
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 1);
        $this->assertEquals($uData[0], $this->data['userId13']);

        $query = '`meta`="user meta 1"';
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 2);
        $this->assertEquals($uData[0], $this->data['userId1']);
        $this->assertEquals($uData[1], $this->data['userId13']);

        $query = '`userId`="NotFound"';
        $this->assertTrue($this->userData->where($query)->status === false);
    }

    public function testWhereObject()
    {
        $query = [
            'userId' => 'userId1'
        ];
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 1);
        $this->assertEquals($uData[0], $this->data['userId1']);

        $query = [
            'userId' => 'userId3',
            'meta' => 'user meta 1'
        ];
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 1);
        $this->assertEquals($uData[0], $this->data['userId13']);

        $query = [
            'meta' => 'user meta 1'
        ];
        $uData = $this->userData->where($query)->data;
        $this->assertTrue(count($uData) === 2);
        $this->assertEquals($uData[0], $this->data['userId1']);
        $this->assertEquals($uData[1], $this->data['userId13']);

        $query = [
            'userId' => 'NotFound'
        ];
        $this->assertTrue($this->userData->where($query)->status === false);
    }

    public function testWhereOne()
    {
        $query = '`userId`="userId1"';
        $this->assertTrue($this->userData->whereOne($query));
        $this->assertEquals($this->userData->data, $this->data['userId1']['data']);

        $query = '`userId`="userId3" AND `meta`="user meta 1"';
        $this->assertTrue($this->userData->whereOne($query));
        $this->assertEquals($this->userData->data, $this->data['userId13']['data']);

        $query = '`userId`="NotFound"';
        $this->assertFalse($this->userData->whereOne($query));
    }

    public function testWhereOneObject()
    {
        $query = [
            'userId' => 'userId1'
        ];
        $this->assertTrue($this->userData->whereOne($query));
        $this->assertEquals($this->userData->data, $this->data['userId1']['data']);

        $query = [
            'userId' => 'userId3',
            'meta' => 'user meta 1'
        ];
        $this->assertTrue($this->userData->whereOne($query));
        $this->assertEquals($this->userData->data, $this->data['userId13']['data']);

        $query = [
            'userId' => 'NotFound'
        ];
        $this->assertFalse($this->userData->whereOne($query));
    }

    public function testDeleteData()
    {
        $query = '`userId`="userId1"';
        $this->userData->userId = 'userId1';
        $this->assertTrue($this->userData->delete());

        $this->assertFalse($this->userData->whereOne($query));
    }

    public function testUpdateData()
    {
        $query = '`meta`="meta"';
        $this->assertFalse($this->userData->whereOne($query));
        $this->userData->userId = 'userId1';
        $this->userData->meta = 'meta';
        $this->userData->data = [];
        $this->assertTrue($this->userData->update());

        $this->assertTrue($this->userData->whereOne($query));
    }

    public function testSaveData()
    {
        $query = '`meta`="meta"';
        $this->assertFalse($this->userData->whereOne($query));
        $this->userData->userId = 'userId5';
        $this->userData->meta = 'meta';
        $this->userData->data = ['name' => 'user 5'];
        $this->assertTrue($this->userData->save());

        $this->assertTrue($this->userData->whereOne($query));
        $this->assertTrue($this->userData->userId === 'userId5');
        $this->assertEquals($this->userData->data, ['name' => 'user 5']);
    }
}
