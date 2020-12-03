<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\controller;


use MM\bot\components\button\Buttons;
use MM\bot\components\card\Card;
use MM\bot\components\nlu\Nlu;
use MM\bot\components\sound\Sound;
use MM\bot\components\standard\Text;
use MM\bot\core\mmApp;


/**
 * Class BotController
 * @package bot\controller
 *
 * Абстрактный класс, который унаследуют все классы, обрабатывающие логику приложения.
 */
abstract class BotController
{
    /**
     * Кнопки отображаемые в приложении
     * @var Buttons $buttons Кнопки отображаемые в приложении
     * @see Buttons Смотри тут
     */
    public $buttons;
    /**
     * Карточки отображаемые в приложении.
     * @var Card $card Карточки отображаемые в приложении.
     * @see Card Смотри тут
     */
    public $card;
    /**
     * Текст который увидит пользователь.
     * @var string $text
     */
    public $text;
    /**
     * Текст который услышит пользователь.
     * @var string|null $tts Текст который услышит пользователь.
     * !Важно, если переменная заполняется для других типов приложения, тогда отправляется запрос в yandex speechkit для преобразования текста в звук.
     * Полученный звук отправляется пользователю как аудио сообщение.
     */
    public $tts;
    /**
     * Обработанный nlu в приложении.
     * @var Nlu $nlu Обработанный nlu в приложении.
     * @link [nlu](https://www.maxim-m.ru/glossary/nlu)
     * @see Nlu Смотри тут
     */
    public $nlu;
    /**
     * Звуки в приложении.
     * @var Sound $sound Звуки в приложении.
     * @see Sound Смотри тут
     */
    public $sound;

    /**
     * Идентификатор пользователя.
     * @var string|null $userId Идентификатор пользователя.
     */
    public $userId;
    /**
     * Пользовательский токен. Инициализируется тогда, когда пользователь авторизован (Актуально для Алисы).
     * @var string|null $userToken Пользовательский токен. Инициализируется тогда, когда пользователь авторизован (Актуально для Алисы).
     */
    public $userToken;
    /**
     * Meta данные пользователя.
     * @var array|null $userMeta Meta данные пользователя.
     */
    public $userMeta;
    /**
     * Id сообщения(Порядковый номер сообщения), необходим для того, чтобы понять в 1 раз пишет пользователь или нет.
     * @var string|int|null $messageId Id сообщения(Порядковый номер сообщения), необходим для того, чтобы понять в 1 раз пишет пользователь или нет.
     */
    public $messageId;
    /**
     * Запрос пользователь в нижнем регистре.
     * @var string|null $userCommand Запрос пользователь в нижнем регистре.
     */
    public $userCommand;
    /**
     * Оригинальный запрос пользователя.
     * @var string|null $originalUserCommand Оригинальный запрос пользователя.
     */
    public $originalUserCommand;
    /**
     * Дополнительные параметры запроса.
     * @var array|null $payload Дополнительные параметры запроса.
     */
    public $payload;
    /**
     * Пользовательские данные (Хранятся в бд либо в файле. Зависит от переменной mmApp::$isSaveDb).
     * @var array|null $userData Пользовательские данные (Хранятся в бд либо в файле. Зависит от переменой mmApp::$isSaveDb).
     */
    public $userData;
    /**
     * Запросить авторизацию пользователя или нет (Актуально для Алисы).
     * @var bool $isAuth Запросить авторизацию пользователя или нет (Актуально для Алисы).
     */
    public $isAuth;
    /**
     * Проверка что авторизация пользователя прошла успешно (Актуально для Алисы).
     * @var bool|null $isAuthSuccess Проверка что авторизация пользователя прошла успешно (Актуально для Алисы).
     */
    public $isAuthSuccess;

    /**
     * Пользовательское хранилище (Актуально для Алисы).
     * @var array|null $state Пользовательское хранилище (Актуально для Алисы).
     */
    public $state;

    /**
     * Если ли экран (Актуально для Алисы).
     * @var bool $isScreen Если ли экран (Актуально для Алисы).
     */
    public $isScreen;
    /**
     * Завершение сессии (Актуально для Алисы).
     * @var bool $isEnd Завершение сессии (Актуально для Алисы).
     */
    public $isEnd;
    /**
     * Отправлять в конце запрос или нет. (Актуально для Vk и Telegram) False тогда, когда все запросы отправлены внутри логики приложения, и больше ничего отправлять не нужно.
     * @var bool $isSend Отправлять в конце запрос или нет. (Актуально для Vk и Telegram) False тогда, когда все запросы отправлены внутри логики приложения, и больше ничего отправлять не нужно.
     */
    public $isSend;

    /**
     * Полученный запрос.
     * @var array|null $requestObject Полученный запрос.
     */
    public $requestObject;

    /**
     * BotController constructor.
     */
    public function __construct()
    {
        $this->buttons = new Buttons();
        $this->card = new Card();
        $this->nlu = new Nlu();
        $this->sound = new Sound();

        $this->text = '';
        $this->tts = null;
        $this->userId = null;
        $this->userToken = null;
        $this->userMeta = null;
        $this->userCommand = null;
        $this->originalUserCommand = null;
        $this->isScreen = true;
        $this->isEnd = false;
        $this->messageId = null;
        $this->userData = null;
        $this->state = null;
        $this->isAuth = false;
        $this->isAuthSuccess = null;
        $this->isSend = true;
        $this->requestObject = null;
    }

    /**
     * Получение всех обрабатываемых команд приложения.
     *
     * @return array
     */
    protected final function intents(): array
    {
        return mmApp::$params['intents'] ?? [];
    }

    /**
     * Поиск нужной команды в  пользовательском запросе.
     * В случае успеха вернет название действия.
     *
     * @param string $text Текст, в котором происходит поиск вхождений.
     * @return string|null
     */
    protected final function getIntent(string $text): ?string
    {
        $intents = $this->intents();
        foreach ($intents as $intent) {
            if (Text::isSayText(($intent['slots'] ?? []), $text, ($intent['is_pattern'] ?? false))) {
                return $intent['name'];
            }
        }
        return null;
    }

    /**
     * Обработка пользовательских команд.
     *
     * Если intentName === null, значит не удалось найти обрабатываемых команд в тексте.
     * В таком случе стоит смотреть либо на предыдущую команду пользователя(которая сохранена в бд).
     * Либо вернуть текст помощи.
     *
     * @param string|null $intentName Название действия.
     */
    public abstract function action(?string $intentName): void;

    /**
     * Запуск приложения.
     * @api
     */
    public function run(): void
    {
        $intent = $this->getIntent($this->userCommand);
        if ($intent === null && $this->messageId == 0) {
            $intent = WELCOME_INTENT_NAME;
        }
        /**
         * Для стандартных действий параметры заполняются автоматически. Есть возможность переопределить их в action() по названию действия
         */
        switch ($intent) {
            case WELCOME_INTENT_NAME:
                $this->text = Text::getText(mmApp::$params['welcome_text'] ?? '');
                break;
            case HELP_INTENT_NAME:
                $this->text = Text::getText(mmApp::$params['help_text'] ?? '');
                break;
        }

        $this->action($intent);
        if ($this->tts === null && (mmApp::$appType === T_ALISA || mmApp::$appType === T_MARUSIA)) {
            $this->tts = $this->text;
        }
    }
}
