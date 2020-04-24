<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 11.03.2020
 * Time: 12:31
 */

namespace MM\bot\core\types;


use MM\bot\controller\BotController;

/**
 * Class TemplateTypeModel
 * @package bot\core\types
 *
 * Абстрактный класс, который унаследуют все классы, отвечающие за инициализацию параметров, и обработку запросов пользователя
 *
 * @property BotController $controller: Класс с логикой приложения
 * @property string $error: Строка с ошибками работы приложения
 */
abstract class TemplateTypeModel
{
    protected $controller;
    protected $error;
    protected $timeStart;

    public function __construct()
    {
        $this->controller = null;
        $this->error = null;
        $this->initProcessingTime();
    }

    /**
     * Установка начального времени.
     * Необходимо для определения времени выполнения программы.
     */
    private function initProcessingTime(): void
    {
        $this->timeStart = microtime(true);
    }

    /**
     * Получить время выполнения программы
     *
     * @return int|float
     */
    public function getProcessingTime()
    {
        return microtime(true) - $this->timeStart;
    }

    /**
     * @return string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Инициализация основных параметров и компонентов контроллера.
     *
     * @param string|null $content : Запрос пользователя. В основном json строка
     * @param BotController $controller : Ссылка на класс с логикой навык/бота
     * @return bool
     */
    public abstract function init(?string $content, BotController &$controller): bool;

    /**
     * Отправка ответа пользователю
     *
     * @return string
     */
    public abstract function getContext(): string;
}
