<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 07.03.2020
 * Time: 16:48
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
 *
 * @property Buttons $buttons: Кнопки отображаемые в приложении @see class Buttons
 * @see Card
 * @property Card $card: Карточки отображаемые в приложении
 * @property string $text: Текст который увидит пользователь
 * @property string $tts: Текст который услышит пользователь
 * !Важно, если переменная заполняется для других типов приложения, тогда отправляется запрос в yandex speechkit для преобразования текста в звук.
 * Полученный звук отправляется пользователю как аудио сообщение.
 * @see Nlu
 * @property Nlu $nlu: Обработанный nlu(@see (https://www.maxim-m.ru/glossary/nlu)) в приложении
 * @see Sound
 * @property Sound $sound: Звуки в приложении
 *
 * @property string $userId: Идентификатор пользователя
 * @property string $userToken: Пользовательский токен. Инициализируется тогда, когда пользователь авторизован (Актуально для Алисы)
 * @property array $userMeta: Meta данные пользователя
 * @property string $messageId: Id сообщения(Порядковый номер сообщения), необходим для того, чтобы понять в 1 раз пишет пользователь или нет.
 * @property string $userCommand: Запрос пользователь в нижнем регистре
 * @property string $originalUserCommand: Оригинальный запрос пользователя
 * @property array $payload: Дополнительные параметры запроса
 * @property array $userData: Пользовательские данные (Хранятся в бд либо в файле. Зависит от параметра IS_SAVE_DB)
 * @property bool $isAuth: Запросить авторизацию пользователя или нет (Актуально для Алисы)
 * @property bool|null $isAuthSuccess: Проверка что авторизация пользователя прошла успешно (Актуально для Алисы)
 *
 * @property array $state: Пользовательское хранилище (Актуально для Алисы)
 *
 * @property bool $isScreen: Если ли экран (Актуально для Алисы)
 * @property bool $isEnd: Завершение сессии (Актуально для Алисы)
 * @property bool $isSend: Отправлять в конце запрос или нет. (Актуально для Vk и Telegram) False тогда, когда все запросы отправлены внутри логики приложения, и больше ничего отправлять не нужно
 *
 * @property array $requestArray: Полученный запрос
 */
abstract class BotController
{
    public $buttons;
    public $card;
    public $text;
    public $tts;
    public $nlu;
    public $sound;

    public $userId;
    public $userToken;
    public $userMeta;
    public $messageId;
    public $userCommand;
    public $originalUserCommand;
    public $payload;
    public $userData;
    public $isAuth;
    public $isAuthSuccess;

    public $state;

    public $isScreen;
    public $isEnd;
    public $isSend;

    public $requestArray;

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
        $this->requestArray = null;
    }

    /**
     * Получение всех обрабатываемых команд
     *
     * @return array
     */
    protected final function intents(): array
    {
        return mmApp::$params['intents'] ?? [];
    }

    /**
     * Поиск нужной команды в запросе.
     * В случае успеха вернет название действия
     *
     * @param string $text : Текст, в котором происходит поиск вхождений
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
     * Обработка пользовательских команд
     *
     * Если intentName === null, значит не удалось найти обрабатываемых команд в тексте.
     * В таком случе стоит смотреть либо на предыдущую команду пользователя(которая сохранена в бд).
     * Либо вернуть текст помощи.
     *
     * @param string|null $intentName : Название действия
     */
    public abstract function action(?string $intentName): void;

    /**
     * Запуск приложения
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
