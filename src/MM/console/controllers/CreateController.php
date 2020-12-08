<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\console\controllers;


use MM\bot\core\mmApp;

class CreateController
{
    const T_DEFAULT = 'Default';
    const T_QUIZ = 'Quiz';

    protected $path;
    protected $name;
    public $params;

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
        $params = mmApp::arrayMerge($defaultParams, $this->params['params']);

        $content = $this->getHeaderContent();
        $content .= "return ";
        $content .= $this->getArrayToPhpStr($params);
        $content .= ";\n";

        return $content;
    }

    protected function initConfig($defaultConfig)
    {
        $config = mmApp::arrayMerge($defaultConfig, $this->params['config']);

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
        $replace = [
            date('d.m.Y'),
            date('H:i'),
            __DIR__ . '/../../bot',
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
        $configFile = "{$this->path}/config/{{name}}Config.php";
        if (is_file("{$path}/config/{$type}Config.php")) {
            $configContent = $this->initConfig(include "{$path}/config/{$type}Config.php");
        } else {
            $configContent = $this->getFileContent("{$path}/config/{$type}Config.php");
        }
        $this->generateFile($configContent, $configFile);
    }

    protected function getParamsFile(string $path)
    {
        $paramsFile = "{$this->path}/config/{{name}}Params.php";
        if (is_file("{$path}/config/defaultParams.php")) {
            $paramsContent = $this->initConfig(include "{$path}/config/defaultParams.php");
        } else {
            $paramsContent = $this->getFileContent("{$path}/config/defaultParams.php");
        }
        $this->generateFile($paramsContent, $paramsFile);
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
            $controllerFile .= '/{{className}}Controller.php';
            $controllerContent = $this->getFileContent("{$standardPath}/controller/{$type}Controller.php");
            $this->generateFile($controllerContent, $controllerFile);

            $indexFile = "{$this->path}/index.php";
            $indexContent = $this->getFileContent("{$standardPath}/index.php");
            $this->generateFile($indexContent, $indexFile);
        }
    }

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
