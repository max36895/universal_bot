<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\models\db\QueryData;
use PHPUnit\Framework\TestCase;

class QueryDataTest extends TestCase
{

    public function testGetQueryData()
    {
        $result = QueryData::getQueryData('');
        $this->assertTrue($result === null);

        $result = QueryData::getQueryData('`test`=512');
        $this->assertEquals(['test' => 512], $result);

        $result = QueryData::getQueryData('`test`="test"');
        $this->assertEquals(['test' => "test"], $result);

        $result = QueryData::getQueryData('`test1`=512 `test2`="test"');
        $this->assertEquals(['test1' => 512, 'test2' => "test"], $result);

        $result = QueryData::getQueryData('`test`=512 ');
        $this->assertEquals(['test' => 512], $result);
    }
}