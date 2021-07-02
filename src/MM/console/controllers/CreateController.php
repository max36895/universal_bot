<?php

namespace MM\console\controllers;


use MM\bot\core\mmApp;

/**
 * Класс, позволяющий создать проект
 * Class CreateController
 * @package MM\console\controllers
 */
class CreateController
{
    /**
     * Создает пустой проект
     */
    const T_DEFAULT = 'Default';
    /**
     * Создает викторину
     */
    const T_QUIZ = 'Quiz';

    protected $path;
    protected $name;
    /**
     * Параметры для создания приложения
     * @var array
     */
    public $params;

    private function _print(string $str, bool $isError = false)
    {
        $handler = $isError ? STDERR : STDOUT;
        fwrite($handler, "{$str}\n");
    }

    protected function getFileContent($file)
    {
        $content = '';
        if ($file && is_file($file)) {
            $content = file_get_contents($file);
        }
        return $content;
    }

    public function getArrayToPhpStr($arr, $depth = 0): string
    {
        $content = '';
        $tab = "\t";
        for ($i = 0; $i < $depth; $i++) {
            $tab .= "\t";
        }

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $content .= "{$tab}'{$key}' => [\n";
                $content .= $this->getArrayToPhpStr($value, $depth + 1);
                $content .= "{$tab}],\n";
            } else {
                $content .= "{$tab}'{$key}' => '{$value}',\n";
            }
        }
        return $content;
    }

    protected function getHeaderContent()
    {
        $headerContent = "<?php\n";
        $headerContent .= "/*\n";
        $headerContent .= " * Date: {{date}}\n";
        $headerContent .= " * Time: {{time}}\n";
        $headerContent .= " */\n\n";
        return $headerContent;
    }

    protected function initParams($defaultParams)
    {
        $params = mmApp::arrayMerge($defaultParams, $this->params['params'] ?? null);

        $content = $this->getHeaderContent();
        $content .= "return ";
        $content .= $this->getArrayToPhpStr($params);
        $content .= ";\n";

        return $content;
    }

    protected function initConfig($defaultConfig)
    {
        $config = mmApp::arrayMerge($defaultConfig, $this->params['config'] ?? null);

        $content = $this->getHeaderContent();
        $content .= "return ";
        $content .= $this->getArrayToPhpStr($config);
        $content .= ";\n";

        return $content;
    }

    protected function generateFile(string $templateContent, string $fileName): string
    {
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
        $dir = __DIR__ . '/../../bot/init.php';
        if (defined('U_BOT_COMPOSER_INSTALL')) {
            $dir = U_BOT_COMPOSER_INSTALL;
        }
        $replace = [
            date('d.m.Y'),
            date('H:i'),
            $dir,
            $this->name,
            $name
        ];
        $fileName = str_replace($find, $replace, $fileName);
        $content = str_replace($find, $replace, $templateContent);
        file_put_contents($fileName, $content);
        return $fileName;
    }

    protected function getConfigFile(string $path, string $type)
    {
        $this->_print('Создается файл с конфигурацией приложения: ...');
        $configFile = "{$this->path}/config/{{name}}Config.php";
        if (is_file("{$path}/config/{$type}Config.php")) {
            $configContent = $this->initConfig(include "{$path}/config/{$type}Config.php");
        } else {
            $configContent = '';
        }
        $this->generateFile($configContent, $configFile);
        $this->_print('Файл с конфигурацией успешно создан!');
    }

    protected function getParamsFile(string $path)
    {
        $this->_print('Создается файл с параметрами приложения: ...');

        $paramsFile = "{$this->path}/config/{{name}}Params.php";
        if (is_file("{$path}/config/defaultParams.php")) {
            $paramsContent = $this->initConfig(include "{$path}/config/defaultParams.php");
        } else {
            $paramsContent = '';
        }
        $this->generateFile($paramsContent, $paramsFile);
        $this->_print('Файл с параметрами успешно создан!');
    }

    protected function create($type = self::T_DEFAULT)
    {
        if (in_array($type, [self::T_DEFAULT, self::T_QUIZ])) {
            $standardPath = __DIR__ . '/../template';
            $configFile = "{$this->path}/config";
            if (!is_dir($configFile)) {
                mkdir($configFile);
            }
            $typeToLower = strtolower($type);

            $this->getConfigFile($standardPath, $typeToLower);
            $this->getParamsFile($standardPath);

            $controllerFile = "{$this->path}/controller";
            if (!is_dir($controllerFile)) {
                mkdir($controllerFile);
            }

            $this->_print('Создается класс с логикой приложения: ...');
            $controllerFile .= '/{{className}}Controller.php';
            $controllerContent = $this->getFileContent("{$standardPath}/controller/{$type}Controller.php");
            $this->generateFile($controllerContent, $controllerFile);
            $this->_print('Класс с логикой приложения успешно создан!');

            $this->_print('Создается index файл: ...');
            $indexFile = "{$this->path}/index.php";
            $indexContent = $this->getFileContent("{$standardPath}/index.php");
            $this->generateFile($indexContent, $indexFile);
            $this->_print('Index файл успешно создан!');
            
            $this->_print("Проект успешно создан, и находится в директории: {$this->path}");
        } else {
            $this->_print('Не удалось создать проект!', true);
        }
    }

    /**
     * Инициализация параметров проекта
     * @param null $name Имя проекта
     * @param string $type Тип проекта
     */
    public function init($name = null, $type = self::T_DEFAULT)
    {
        if ($name) {
            if (!is_dir($name)) {
                mkdir($name);
            }
            $this->name = $name;
            $this->path = '';
            if ($this->params['path']) {
                $this->path = $this->params['path'];
                $paths = explode('/', $this->path);
                $path = '';
                foreach ($paths as $p) {
                    $path .= $p . '/';
                    if ($p !== './' && $p !== '../') {
                        if (!is_dir($path)) {
                            mkdir($path);
                        }
                    }
                }
            }
            $this->path .= $name;
            $this->create($type);
        }
    }
}
