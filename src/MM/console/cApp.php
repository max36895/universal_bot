<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * Скрипт позволяет создавать/удалять БД, а также создавать шаблон для приложения.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */
require_once __DIR__ . '/controllers/ConsoleController.php';

$console = new MM\console\controllers\ConsoleController();

$param = null;
if ($argv[1]) {
    $param['command'] = strtolower($argv[0]);
    if (strpos($argv[1], '.json') !== -1) {
        if (is_file($argv[1])) {
            $jsonParam = json_decode(file_get_contents($argv[1]));
            if ($jsonParam['config'] && is_file($jsonParam['config'])) {
                MM\bot\core\mmApp::setConfig(include $jsonParam['config']);
            }
            $param['appName'] = $jsonParam['name'];
            $param['params'] = $jsonParam;
        }
    } else {
        $param['appName'] = $argv[1];
    }
}

$console->run($param);
