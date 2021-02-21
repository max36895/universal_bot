<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
require_once __DIR__ . '/../../../src/MM/bot/init.php';
require_once __DIR__ . '/controller/UserAppController.php';
require_once __DIR__ . '/UserTemplate/Controller/UserApp.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/../../config/skillStorageConfig.php');
$bot->initParams(include __DIR__ . '/../../config/skillDefaultParam.php');
$logic = new UserAppController();
$bot->initBotController($logic);

$userApp = new UserApp();
//echo $bot->run($userApp);
/**
 * Отображаем ответ навыка и хранилище в консоли.
 */
$bot->test(true,
    true,
    true,
    $userApp,
    __DIR__ . '/UserTemplate/userDataConfig.php'
);
