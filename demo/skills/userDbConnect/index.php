<?php
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/StandardController.php';
require_once __DIR__ . '/dbConnect/DbConnect.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../../config/skillDefaultConfig.php');
$bot->initParams(include __DIR__ . '/../../config/skillDefaultParam.php');
$logic = new StandardController();
\MM\bot\core\mmApp::$userDbController = new DbConnect();
$bot->initBotController($logic);
//echo $bot->run();
$bot->test();
