<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 03.03.2021
 * Time: 15:28
 */
require_once __DIR__ . '/../../src/MM/bot/components/standard/Text.php';

use MM\bot\components\standard\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testResize()
    {
        $this->assertSame('test te', Text::resize('test te'));
        $this->assertSame('testing', Text::resize('testing te', 7, false));
        $this->assertSame('test...', Text::resize('testing te', 7));
        $this->assertSame('test...', Text::resize('testing te', 7, true));
    }

    public function testIsSayTrue()
    {
        $this->assertTrue(Text::isSayTrue('конечно да'));
        $this->assertTrue(Text::isSayTrue('наверное да'));
        $this->assertTrue(Text::isSayTrue('согласен'));
        $this->assertTrue(Text::isSayTrue('согласна'));
        $this->assertTrue(Text::isSayTrue('подтверждаю'));
        $this->assertTrue(Text::isSayTrue('не знаю но да наверное'));
        $this->assertTrue(Text::isSayTrue('конечно не дам тебе'));

        $this->assertFalse(Text::isSayTrue('наша дама пошла'));
        $this->assertFalse(Text::isSayTrue('неа'));
    }

    public function testIsSayFalse()
    {
        $this->assertFalse(Text::isSayFalse('конечно да'));
        $this->assertFalse(Text::isSayFalse('наверное да'));

        $this->assertTrue(Text::isSayFalse('не согласен'));
        $this->assertFalse(Text::isSayFalse('согласен'));

        $this->assertTrue(Text::isSayFalse('не согласна'));
        $this->assertFalse(Text::isSayFalse('согласна'));

        $this->assertFalse(Text::isSayFalse('подтверждаю'));
        $this->assertFalse(Text::isSayFalse('небоскреб'));
        $this->assertFalse(Text::isSayFalse('пока думаю, но наверное да'));

        $this->assertTrue(Text::isSayFalse('конечно не дам тебе'));
        $this->assertTrue(Text::isSayFalse('неа'));
        $this->assertTrue(Text::isSayFalse('наверное нет'));
        $this->assertTrue(Text::isSayFalse('наверное нет но я надо подумать'));
    }

    public function testIsSayText()
    {
        $this->assertTrue(Text::isSayText('да', 'куда', true));
        $this->assertTrue(Text::isSayText('да', 'куда'));
        $this->assertFalse(Text::isSayText('(?:^|\s)да\b', 'куда'));

        $text = 'По полю шол человек, который сильно устал. Но он н отчаивался и пошел спать';

        $this->assertTrue(Text::isSayText('спать', $text));
        $this->assertTrue(Text::isSayText(['пошел', 'утопал'], $text));
    }

    public function testGetEnding()
    {
        $this->assertSame(Text::getEnding(1, ['яблоко', 'яблока', 'яблок']), 'яблоко');
        $this->assertSame(Text::getEnding(2, ['яблоко', 'яблока', 'яблок']), 'яблока');
        $this->assertSame(Text::getEnding(3, ['яблоко', 'яблока', 'яблок']), 'яблока');
        $this->assertSame(Text::getEnding(4, ['яблоко', 'яблока', 'яблок']), 'яблока');

        for ($i = 5; $i < 21; $i++) {
            $this->assertSame(Text::getEnding($i, ['яблоко', 'яблока', 'яблок']), 'яблок');
        }

        $this->assertSame(Text::getEnding(21, ['яблоко', 'яблока', 'яблок']), 'яблоко');
        $this->assertSame(Text::getEnding(22, ['яблоко', 'яблока', 'яблок']), 'яблока');
        $this->assertSame(Text::getEnding(29, ['яблоко', 'яблока', 'яблок']), 'яблок');
    }
}