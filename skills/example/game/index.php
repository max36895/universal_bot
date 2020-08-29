<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 29.08.2020
 * Time: 11:50
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/GameController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../config/skillGameConfig.php');
$bot->initParams(include __DIR__ . '/../config/skillGameParam.php');
$logic = new GameController();
$bot->initBotController($logic);
//echo $bot->run();
$bot->test(true);
