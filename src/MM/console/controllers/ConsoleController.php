<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 13.03.2020
 * Time: 13:34
 */

namespace console\controllers;

require_once __DIR__.'/../../bot/components/standard/Text.php';
require_once __DIR__.'/../../bot/core/mmApp.php';
require_once __DIR__.'/../../bot/models/db/Sql.php';
require_once __DIR__.'/../../bot/models/db/Model.php';
require_once __DIR__.'/../../bot/models/ImageTokens.php';
require_once __DIR__.'/../../bot/models/SoundTokens.php';
require_once __DIR__.'/../../bot/models/UsersData.php';

class ConsoleController
{
    public function run($argv)
    {
        if ($argv) {
            switch ($argv[0]) {
                case 'init':
                    $init = new InitController();
                    $init->init();
                    break;
                case 'create':
                    $create = new CreateController();
                    $create->init($argv[1] ?? null);
                    break;
            }
        } else {
            echo "Передайте параметры!\nДоступные параметры:\ninit - создание бд;\ncreate (project name) - Создать новый навык/бот. В качестве параметра передается название проекта(На Английском языке)";
        }
    }
}
