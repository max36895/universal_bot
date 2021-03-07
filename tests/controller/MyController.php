<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 03.03.2021
 * Time: 22:30
 */
require_once __DIR__ . '/../../src/MM/bot/init.php';

use MM\bot\controller\BotController;

class MyController extends BotController
{
    public function action(?string $intentName): void
    {
        // TODO: Implement action() method.
    }

    public function testIntent(string $text):?string
    {
        return $this->getIntent($text);
    }

    public function testIntents(): array
    {
        return $this->intents();
    }
}