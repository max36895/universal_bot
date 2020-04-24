<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 10.03.2020
 * Time: 13:31
 */
require_once __DIR__ . '/../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/ExampleController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$configs = [
    'json' => __DIR__ . '/json',
    'error_log' => __DIR__ . '/errors',
];
$bot->initConfig($configs);
$bot->initParams(include __DIR__ . '/config/skillConfig.php');
$logic = new ExampleController();
$bot->initBotController($logic);
//echo $bot->run();
$bot->test();
