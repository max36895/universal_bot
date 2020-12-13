<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\core\types;


use MM\bot\controller\BotController;

/**
 * Class TemplateTypeModel
 * @package bot\core\types
 *
 * Абстрактный класс, который унаследуют все классы, отвечающие за инициализацию параметров, и обработку запросов пользователя.
 */
abstract class TemplateTypeModel
{
    /**
     * Класс с логикой приложения.
     * @var BotController|null $controller
     */
    protected $controller;
    /**
     * Строка с ошибками, произошедшими при работе приложения.
     * @var string|null $error
     */
    protected $error;
    /**
     * Время начала работы приложения.
     * @var float|int|null $timeStart
     */
    protected $timeStart;
    /**
     * Использование локального хранилища как БД.
     * @var bool $isUsedLocalStorage
     */
    public $isUsedLocalStorage;

    public function __construct()
    {
        $this->controller = null;
        $this->error = null;
        $this->initProcessingTime();
        $this->isUsedLocalStorage = false;
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
     * Получить время выполнения программы.
     *
     * @return int|float
     * @api
     */
    public function getProcessingTime()
    {
        return microtime(true) - $this->timeStart;
    }

    /**
     * Получение текста с ошибкой при выполнении программы.
     *
     * @return string|null
     * @api
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Инициализация основных параметров и компонентов контроллера.
     *
     * @param string|null $content Запрос пользователя. В основном json строка.
     * @param BotController $controller Ссылка на класс с логикой навык/бота.
     * @return bool
     */
    public abstract function init(?string $content, BotController &$controller): bool;

    /**
     * Отправка ответа пользователю.
     *
     * @return string
     */
    public abstract function getContext(): string;

    /**
     * Доступно ли использование локального хранилища.
     * Если доступно, и используется опция для сохранения данных в хранилище,
     * тогда пользовательские данные не будут сохраняться в БД.
     *
     * @return bool
     * @api
     */
    public function isLocalStorage(): bool
    {
        return false;
    }

    /**
     * Возвращает данные из хранилища.
     *
     * @return array|null
     * @api
     */
    public function getLocalStorage(): ?array
    {
        return null;
    }
}
