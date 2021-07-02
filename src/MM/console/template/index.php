<?php
/**
 * Created by u_bot
 * Date: {{date}}
 * Time: {{time}}
 */

require_once '{{__BotDir__}}';
require_once __DIR__ . '/controller/__className__Controller.php';

$bot = new MM\bot\core\Bot();
$bot->initTypeInGet();
$bot->initConfig(include __DIR__ . '/config/{{name}}Config.php');
$bot->initParams(include __DIR__ . '/config/{{name}}Params.php');
$bot->initBotController((new __className__Controller()));
echo $bot->run();
