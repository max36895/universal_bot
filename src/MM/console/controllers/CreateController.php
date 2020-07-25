<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 13.03.2020
 * Time: 11:02
 */

namespace MM\console\controllers;


class CreateController
{
    const T_DEFAULT = 'default';

    protected $path;
    protected $name;

    protected function createConfig()
    {
        $configDir = $this->path . '/config';
        if (!is_dir($configDir)) {
            mkdir($configDir);
        }
        $fConfig = fopen($configDir . "/{$this->name}Config.php", 'w');
        $dConfig = "<?php\n";
        $dConfig .= "/**\n";
        $dConfig .= " * Created by Maxim-M bot\n";
        $dConfig .= " * Date: " . date('d.m.Y') . "\n";
        $dConfig .= " * Time: " . date('H:i') . "\n";
        $dConfig .= " */\n";
        $dConfig .= "return [\n";
        $dConfig .= "\t'telegram_token' => '',\n";
        $dConfig .= "\t'vk_api_version' => '',\n";
        $dConfig .= "\t'vk_confirmation_token' => '',\n";
        $dConfig .= "\t'vk_token' => '',\n";
        $dConfig .= "\t'yandex_token' => '',\n";
        $dConfig .= "\t'welcome_text' => '',\n";
        $dConfig .= "\t'help_text' => '',\n";
        $dConfig .= "\t'intents' => [\n";
        $dConfig .= "\t\t[\n";
        $dConfig .= "\t\t\t'name' => '',\n";
        $dConfig .= "\t\t\t'slots' => []'\n";
        $dConfig .= "\t\t]\n";
        $dConfig .= "\t]\n";
        $dConfig .= "];\n";
        fwrite($fConfig, $dConfig);
        fclose($fConfig);
        return "/config/{$this->name}Config.php";
    }

    protected function createController()
    {
        $configDir = $this->path . '/controller';
        if (!is_dir($configDir)) {
            mkdir($configDir);
        }
        $name = (mb_strtoupper(mb_substr($this->name, 0, 1))) . (mb_substr($this->name, 1));
        $filePath = "/controller/{$name}Controller.php";
        $fController = fopen($this->path . $filePath, 'w');
        $dController = "<?php\n";
        $dController .= "/**\n";
        $dController .= " * Created by Maxim-M bot\n";
        $dController .= " * Date: " . date('d.m.Y') . "\n";
        $dController .= " * Time: " . date('H:i') . "\n";
        $dController .= " */\n";
        $dController .= "\n";
        $dController .= "class {$name}Controller extends \bot\controller\BotController\n";
        $dController .= "{\n";
        $dController .= "/**\n";
        $dController .= " * Обработка команд\n";
        $dController .= " * @property string \$intentName\n";
        $dController .= " */\n";
        $dController .= "\tpublic function action(\$intentName): void\n";
        $dController .= "\t{\n";
        $dController .= "\t\tswitch (\$intentName) {\n";
        $dController .= "\t\t\t\n";
        $dController .= "\t\t}\n";
        $dController .= "\t}\n";
        $dController .= "}\n";
        fwrite($fController, $dController);
        fclose($fController);
        return $filePath;
    }

    protected function createIndex($controllerPath, $configPath)
    {
        $initPath = __DIR__ . '/../../bot/init.php';
        $controllerClassName = str_replace(['/controller/', '.php'], '', $controllerPath);
        $fIndex = fopen('index.php', 'w');
        $dIndex = "<?php\n";
        $dIndex .= "/**\n";
        $dIndex .= " * Created by Maxim-M bot\n";
        $dIndex .= " * Date: " . date('d.m.Y') . "\n";
        $dIndex .= " * Time: " . date('H:i') . "\n";
        $dIndex .= " */\n";
        $dIndex .= "\n";
        $dIndex .= "require_once '{$initPath}'; // Подключение основных компонентов приложения\n";
        $dIndex .= "require_once __DIR__ . '{$controllerPath}'; // Сгенерированный контролер, отвечающий за логику\n";
        $dIndex .= "\n";
        $dIndex .= "\$bot = new \bot\core\Bot(); // Создаем приложение\n";
        $dIndex .= "\$bot->initTypeInGet(); // Отпеделяем тип приложения(alisa, vk, telegram) через _GET['type']\n";
        $dIndex .= "\$bot->initConfig(include __DIR__ . '{$configPath}'); // Устанавливаем настройки\n";
        $dIndex .= "\$logic = new {$controllerClassName}(); // Создаем объект с логикой навыка/бота\n";
        $dIndex .= "\$bot->initBotLogic(\$logic); // Инициализируем логику приложения\n";
        $dIndex .= "echo \$bot->run() // Запускаем приложение\n";
        fwrite($fIndex, $dIndex);
        fclose($fIndex);
    }

    protected function generateFile(string $templateFile, string $newFileName)
    {
        $templateContent = file_get_contents(__DIR__ . "/../../bot/template/{$templateFile}");
        $find = [
            '{{date}}',
            '{{time}}',
            '{{__BotDir__}}',
            '{{name}}',
            '{{className}}',
            '__className__',
            '{{}}',
        ];
        $name = (mb_strtoupper(mb_substr($this->name, 0, 1))) . (mb_substr($this->name, 1));
        $replace = [
            date('d.m.Y'),
            date('H:i'),
            __DIR__ . '/../../bot',
            $this->name,
            $name
        ];
        $newFileName = str_replace($find, $replace, $newFileName);
        $content = str_replace($find, $replace, $templateContent);
        file_put_contents($newFileName, $content);
    }

    protected function create($type = self::T_DEFAULT)
    {
        switch ($type) {
            case self::T_DEFAULT:
                $standardPath = 'default/';
                $configFile = "{$this->path}/config";
                if (!is_dir($configFile)) {
                    mkdir($configFile);
                }
                $configFile .= '/{{name}}Config.php';
                $this->generateFile("{$standardPath}/config/defaultConfig.php", $configFile);

                $paramsFile = $this->path . '/config/{{name}}Params.php';
                $this->generateFile("{$standardPath}/config/defaultParams.php", $paramsFile);

                $controllerFile = "{$this->path}/controller";
                if (!is_dir($controllerFile)) {
                    mkdir($controllerFile);
                }
                $controllerFile .= '/{{className}}Controller.php';
                $this->generateFile("{$standardPath}/controller/DefaultController.php", $controllerFile);

                $indexFile = "{$this->path}/index.php";
                $this->generateFile("{$standardPath}/index.php", $indexFile);
                break;
        }
    }

    public function init($name = null)
    {
        if ($name) {
            if (is_dir($name)) {
                printf("Не удалось создать директорию:\n\t%s\nПроверьте права...\n", $name);
                return;
            }
            $this->name = $name;
            mkdir($name);
            $this->path = $name;
            $configPath = $this->createConfig();
            $controllerPath = $this->createController();
            $this->createIndex($controllerPath, $configPath);
        }
    }
}
