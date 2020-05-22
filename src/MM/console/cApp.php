<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 13.03.2020
 * Time: 10:53
 */
require_once __DIR__ . '/controllers/ConsoleController.php';

$console = new MM\console\controllers\ConsoleController();
MM\bot\core\mmApp::setConfig(include __DIR__ . '/config/config.php');
$console->run($argv);
