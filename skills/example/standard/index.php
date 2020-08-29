<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 10.03.2020
 * Time: 13:31
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/StandardController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../config/skillDefaultConfig.php');
$bot->initParams(include __DIR__ . '/../config/skillDefaultParam.php');
$logic = new GameController();
$bot->initBotController($logic);
//echo $bot->run();
$bot->test();
