<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 29.08.2020
 * Time: 11:35
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/LocalStorageController.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../config/skillStorageConfig.php');
$bot->initParams(include __DIR__ . '/../config/skillDefaultParam.php');
$logic = new LocalStorageController();
$bot->initBotController($logic);
//echo $bot->run();
/**
 * Отображаем ответ навыка и хранилище в консоли.
 */
$bot->test(true, true);
