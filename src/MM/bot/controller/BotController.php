<?php

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
 * Абстрактный класс, который должны унаследовать все классы, обрабатывающие логику приложения.
 */
abstract class BotController
{
    /**
     * Кнопки отображаемые в приложении.
     * @var Buttons $buttons
     * @see Buttons Смотри тут
     */
    public $buttons;
    /**
     * Карточки отображаемые в приложении.
     * @var Card $card
     * @see Card Смотри тут
     */
    public $card;
    /**
     * Текст который отобразится пользователю.
     * @var string $text
     */
    public $text;
    /**
     * Текст который услышит пользователь.
     * !Важно, если переменная заполняется для других типов приложения, тогда отправляется запрос в yandex speechkit для преобразования текста в звук.
     * Полученный звук отправляется пользователю как аудио сообщение.
     * @var string|null $tts
     */
    public $tts;
    /**
     * Обработанный nlu в приложении.
     * @var Nlu $nlu
     * @link [nlu](https://www.maxim-m.ru/glossary/nlu)
     * @see Nlu Смотри тут
     */
    public $nlu;
    /**
     * Звуки которые будут присутствовать в приложении.
     * @var Sound $sound
     * @see Sound Смотри тут
     */
    public $sound;

    /**
     * Идентификатор пользователя.
     * @var string|null $userId
     */
    public $userId;
    /**
     * Пользовательский токен. Инициализируется когда пользователь авторизовался (Актуально для Алисы).
     * @var string|null $userToken
     */
    public $userToken;
    /**
     * Meta данные пользователя.
     * @var array|null $userMeta
     */
    public $userMeta;
    /**
     * Id сообщения(Порядковый номер сообщения), необходим для того, чтобы понять в 1 раз пишет пользователь или нет.
     * @var string|int|null $messageId
     */
    public $messageId;
    /**
     * Запрос пользователя в нижнем регистре.
     * @var string|null $userCommand
     */
    public $userCommand;
    /**
     * Оригинальный запрос пользователя.
     * @var string|null $originalUserCommand
     */
    public $originalUserCommand;
    /**
     * Дополнительные параметры к запросу.
     * @var array|null $payload
     */
    public $payload;
    /**
     * Пользовательские данные, сохраненные в приложении (Хранятся в бд либо в файле. Зависит от параметра mmApp.isSaveDb).
     * @var array|null $userData
     */
    public $userData;
    /**
     * Запросить авторизацию для пользователя или нет (Актуально для Алисы).
     * @var bool $isAuth
     */
    public $isAuth;
    /**
     * Проверка что авторизация пользователя прошла успешно (Актуально для Алисы).
     * @var bool|null $isAuthSuccess
     */
    public $isAuthSuccess;

    /**
     * Пользовательское локальное хранилище (Актуально для Алисы).
     * @var array|null $state
     */
    public $state;

    /**
     * Наличие экрана.
     * @var bool $isScreen
     */
    public $isScreen;
    /**
     * Завершение сессии.
     * @var bool $isEnd
     */
    public $isEnd;
    /**
     * Отправлять в конце запрос или нет. (Актуально для Vk и Telegram) False тогда, когда все запросы отправлены внутри логики приложения, и больше ничего отправлять не нужно.
     * @var bool $isSend
     */
    public $isSend;

    /**
     * Полученный запрос.
     * @var array|null $requestObject
     */
    public $requestObject;

    /**
     * Идентификатор предыдущего действия пользователя.
     * @var string|null $oldIntentName
     */
    public $oldIntentName;

    /**
     * Идентификатор текущего действия пользователя.
     * @var string|null $thisIntentName
     */
    public $thisIntentName;

    /**
     * Эмоция, с которой будет общаться приложение. Актуально для Сбер.
     * @var string|null $emotion
     */
    public $emotion;

    /**
     * Манера общения с пользователем. Общаемся на "Вы" или на "ты".
     * Возможные значения:
     * "official" - официальный тон общения(на Вы)
     * "no_official" - Общаемся на ты
     * null - можно использовать любой тон
     * Актуально для Сбер
     * @var string|null $appeal
     * @default null
     */
    public $appeal;

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
        $this->oldIntentName = null;
        $this->thisIntentName = null;
        $this->emotion = null;
        $this->appeal = null;
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
     * Поиск нужной команды в пользовательском запросе.
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
     * Если intentName === null, значит не удалось найти обрабатываемых команд в запросе.
     * В таком случе стоит смотреть либо на предыдущую команду пользователя.
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
        $intent = $this->getIntent(strtolower($this->userCommand));
        if ($intent === null && $this->originalUserCommand && $this->userCommand !== $this->originalUserCommand) {
            $intent = $this->getIntent(strtolower($this->originalUserCommand));
        }
        if ($intent === null && $this->messageId === 0) {
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
