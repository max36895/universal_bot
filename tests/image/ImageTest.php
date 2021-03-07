<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\components\image\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testImageInit()
    {
        $image = new Image();
        \MM\bot\core\mmApp::$params['utm_text'] = '';

        $this->assertFalse($image->init('test', ''));

        $this->assertTrue($image->init('test', 'title'));
        $this->assertEquals($image->title, 'title');
        $this->assertEquals($image->desc, ' ');
        $this->assertTrue($image->imageDir === null);
        $this->assertEquals($image->imageToken, 'test');

        $this->assertTrue($image->init('test', 'title', 'desc'));
        $this->assertEquals($image->desc, 'desc');

        $this->assertTrue($image->init('https://google.com/image.png', 'title', 'desc'));
        $this->assertTrue($image->imageToken === null);

        $this->assertTrue($image->init('test', 'title', 'desc', 'btn'));

        $this->assertSame($image->button->buttons[0]->title, 'btn');
        $this->assertTrue($image->button->buttons[0]->url === null);

        $this->assertTrue($image->init('test', 'title', 'desc', ['title' => 'btn', 'url' => 'https://google.com']));
        $this->assertEquals($image->button->buttons[1]->title, 'btn');
        $this->assertEquals($image->button->buttons[1]->url, 'https://google.com');
    }

    public function testImageInitIsToken()
    {
        $image = new Image();
        $image->isToken = true;
        $this->assertTrue($image->init('https://google.com/$image->png', 'title', 'desc'));
        $this->assertEquals($image->imageToken, 'https://google.com/$image->png');
    }
}