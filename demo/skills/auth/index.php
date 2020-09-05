<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/LocalStorageController.php';

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
