<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 03.03.2021
 * Time: 15:28
 */
require_once __DIR__ . '/../../src/MM/bot/components/standard/Text.php';
require_once __DIR__ . '/../../src/MM/bot/components/standard/Navigation.php';

use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    /**
     * @var \MM\bot\components\standard\Navigation
     */
    protected $navigation;
    protected $elements;

    protected function assertPreConditions()
    {
        $this->navigation = new \MM\bot\components\standard\Navigation();
        $this->elements = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    }

    public function testGetMaxPage()
    {
        $this->assertSame($this->navigation->maxVisibleElements, 5);
        $this->assertSame($this->navigation->thisPage, 0);
        $this->assertSame($this->navigation->getMaxPage($this->elements), 2);
        $this->elements[] = 11;
        $this->assertSame($this->navigation->getMaxPage($this->elements), 3);
    }

    public function testGetElements()
    {
        $this->navigation;
        $tmpElements = $this->navigation->nav($this->elements, '');
        $this->assertSame($tmpElements, [1, 2, 3, 4, 5]);

        $tmpElements = $this->navigation->nav($this->elements, 'дальше');
        $this->assertSame($this->navigation->thisPage, 1);
        $this->assertSame($tmpElements, [6, 7, 8, 9, 10]);

        $tmpElements = $this->navigation->nav($this->elements, 'дальше');
        $this->assertSame($this->navigation->thisPage, 1);
        $this->assertSame($tmpElements, [6, 7, 8, 9, 10]);

        $tmpElements = $this->navigation->nav($this->elements, 'назад');
        $this->assertSame($this->navigation->thisPage, 0);
        $this->assertSame($tmpElements, [1, 2, 3, 4, 5]);

        $tmpElements = $this->navigation->nav($this->elements, 'назад');
        $this->assertSame($this->navigation->thisPage, 0);
        $this->assertSame($tmpElements, [1, 2, 3, 4, 5]);
    }

    public function testSelectedNumberPage()
    {
        $this->navigation->elements = $this->elements;
        $this->assertTrue($this->navigation->numberPage('1 страница'));
        $this->assertSame($this->navigation->thisPage, 0);

        $this->assertTrue($this->navigation->numberPage('2 страница'));
        $this->assertSame($this->navigation->thisPage, 1);

        $this->assertTrue($this->navigation->numberPage('3 страница'));
        $this->assertSame($this->navigation->thisPage, 1);

        $this->assertTrue($this->navigation->numberPage('-2 страница'));
        $this->assertSame($this->navigation->thisPage, 0);
    }

    public function testSelectedElement()
    {
        $this->navigation->elements = $this->elements;
        $selectedElement = $this->navigation->selectedElement($this->elements, '2');
        $this->assertSame($selectedElement, 2);
        $this->elements = [];
        for ($i = 0; $i < 10; $i++) {
            $this->elements[] = [
                'id' => $i + 1,
                'title' => "привет{$i}"
            ];
        }
        $this->elements[3]['title'] = 'приветствую тебя мир';

        $selectedElement = $this->navigation->selectedElement($this->elements, '2');
        $this->assertSame($selectedElement, ['id' => 2, 'title' => 'привет1']);

        $selectedElement = $this->navigation->selectedElement($this->elements, 'приветствую тебя мир', ['title']);
        $this->assertSame($selectedElement, ['id' => 4, 'title' => 'приветствую тебя мир']);

        $selectedElement = $this->navigation->selectedElement($this->elements, 'привет', ['title'], 1);
        $this->assertSame($selectedElement, ['id' => 10, 'title' => 'привет9']);

        $selectedElement = $this->navigation->selectedElement($this->elements, 'пока', ['title'], 1);
        $this->assertSame($selectedElement, null);
    }

    public function testPageNavigationArrow()
    {
        $this->navigation->elements = $this->elements;
        $this->assertSame($this->navigation->getPageNav(), ['Дальше 👉']);
        $this->navigation->thisPage = 1;
        $this->assertSame($this->navigation->getPageNav(), ['👈 Назад']);
        $this->navigation->maxVisibleElements = 2;
        $this->assertSame($this->navigation->getPageNav(), ['👈 Назад', 'Дальше 👉']);
    }

    public function testPageNavigationNumber()
    {
        $this->navigation->elements = $this->elements;
        $this->assertEquals($this->navigation->getPageNav(true), ['[1]', 2]);
        $this->navigation->thisPage = 1;
        $this->assertEquals($this->navigation->getPageNav(true), [1, '[2]']);

        $this->navigation->maxVisibleElements = 1;
        $this->navigation->thisPage = 0;
        $this->assertEquals($this->navigation->getPageNav(true), ['[1]', 2, 3, 4, 5, '... 10']);
        $this->navigation->thisPage = 1;
        $this->assertEquals($this->navigation->getPageNav(true), [1, '[2]', 3, 4, 5, '... 10']);
        $this->navigation->thisPage = 2;
        $this->assertEquals($this->navigation->getPageNav(true), [1, 2, '[3]', 4, 5, '... 10']);
        $this->navigation->thisPage = 3;
        $this->assertEquals($this->navigation->getPageNav(true), [1, 2, 3, '[4]', 5, 6, '... 10']);
        $this->navigation->thisPage = 4;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 3, 4, '[5]', 6, 7, '... 10']);
        $this->navigation->thisPage = 5;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 4, 5, '[6]', 7, 8, '... 10']);
        $this->navigation->thisPage = 6;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 5, 6, '[7]', 8, 9, 10]);
        $this->navigation->thisPage = 7;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 6, 7, '[8]', 9, 10]);
        $this->navigation->thisPage = 8;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 7, 8, '[9]', 10]);
        $this->navigation->thisPage = 9;
        $this->assertEquals($this->navigation->getPageNav(true), ['1 ...', 8, 9, '[10]']);
    }
}