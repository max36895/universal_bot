<?php
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/AuthController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../../config/skillStorageConfig.php');
$bot->initParams(include __DIR__ . '/../../config/skillAuthParam.php');
$logic = new AuthController();
$bot->initBotController($logic);
//echo $bot->run();
/**
 * Отображаем ответ навыка и хранилище в консоли.
 */
$bot->test(true, true);
