<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
require_once __DIR__ . '/controllers/ConsoleController.php';

$console = new MM\console\controllers\ConsoleController();
MM\bot\core\mmApp::setConfig(include __DIR__ . '/config/config.php');
$console->run($argv);
