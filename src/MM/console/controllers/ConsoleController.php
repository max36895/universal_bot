<?php

namespace MM\console\controllers;

use Exception;

class ConsoleController
{
    const VERSION = '1.0';

    /**
     * @param array $param
     * @throws Exception
     */
    public static function main($param = ['appName' => null, 'command' => null])
    {
        $infoText = "Доступные параметры:\ninit-db - создание бд;\ndrop-db - удаление бд;\ncreate (project name) - Создать новый навык/бот. В качестве параметра передается название проекта(На Английском языке) или json файл с параметрами.";
        if ($param && $param['command']) {
            switch ($param['command']) {
                case 'init-db':
                    $init = new InitController();
                    $init->init();
                    break;

                case 'drop-db':
                    $init = new InitController();
                    $init->drop();
                    break;

                case 'create':
                    $create = new CreateController();
                    $create->params = $param['params'];
                    $type = CreateController::T_DEFAULT;
                    if ($param['params']['type']) {
                        $pType = strtolower($param['params']['type']);
                        $pType = (mb_strtoupper(mb_substr($pType, 0, 1))) . (mb_substr($pType, 1));
                        if (in_array($pType, [CreateController::T_DEFAULT, CreateController::T_QUIZ])) {
                            $type = $pType;
                        } else {
                            throw new Exception('Указан не поддерживаемый тип для создания шаблона!');
                        }
                    }
                    $create->init($param['appName'] ?? null, $type);
                    break;

                case '-v':
                case 'version':
                    echo 'version: ' . self::VERSION . "\n";
                    break;

                default:
                    echo $infoText;
                    break;
            }
        } else {
            echo $infoText;
        }
    }
}
