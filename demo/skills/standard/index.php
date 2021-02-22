<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/StandardController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../../config/skillDefaultConfig.php');
$bot->initParams(include __DIR__ . '/../../config/skillDefaultParam.php');
$logic = new StandardController();
$bot->initBotController($logic);
//echo $bot->run();
$bot->test();
