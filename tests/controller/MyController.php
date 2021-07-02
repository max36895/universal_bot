<?php
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