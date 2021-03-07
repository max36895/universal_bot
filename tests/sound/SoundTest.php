<?php
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\components\sound\types\AlisaSound;
use PHPUnit\Framework\TestCase;

class SoundTest extends TestCase
{
    public function testAlisaGetPause(){
        $this->assertEquals(AlisaSound::getPause(1), 'sil <[1]>');
        $this->assertEquals(AlisaSound::getPause(10), 'sil <[10]>');
        $this->assertEquals(AlisaSound::getPause(100), 'sil <[100]>');
        $this->assertEquals(AlisaSound::getPause(1000), 'sil <[1000]>');
    }
    public function testAlisaRemoveSound(){
        $speakAudio = '<speaker audio="alice-sounds-game-win-1.opus">';
        $speakEffect = '<speaker effect="alice-sounds-game-win-1.opus">';
        $pause = AlisaSound::getPause(12);
        $this->assertEquals(AlisaSound::removeSound("{$speakAudio}1{$speakAudio}"), '1');
        $this->assertEquals(AlisaSound::removeSound("{$speakEffect}1{$speakEffect}"), '1');
        $this->assertEquals(AlisaSound::removeSound("{$pause}1{$pause}"), '1');
        $this->assertEquals(AlisaSound::removeSound("{$speakEffect}1{$speakAudio}1{$pause}"), '11');
    }
    public function testGetSounds(){
        $sound = new \MM\bot\components\sound\Sound();
        \MM\bot\core\mmApp::$appType = T_ALISA;
        $this->assertEquals($sound->getSounds('hello'), 'hello');
        $sound->sounds = [
            [
                'key'=> '[{test}]',
                'sounds'=> [
                '<my_Sound>'
            ]
            ]
        ];
        $this->assertEquals($sound->getSounds('hello'), 'hello');
        $this->assertEquals($sound->getSounds('hello [{test}] listen'), 'hello <my_Sound> listen');
        \MM\bot\core\mmApp::$appType = null;
        $this->assertEquals($sound->getSounds('hello [{test}] listen'), 'hello [{test}] listen');
    }
}