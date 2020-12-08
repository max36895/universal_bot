<?php
/**
 * Created by u_bot
 * Date: {{date}}
 * Time: {{time}}
 */

use MM\bot\components\standard\Text;

/**
 * Шаблон для викторины
 * Class __className__Controller
 */
class __className__Controller extends MM\bot\controller\BotController
{
    public $question;
    public const START_QUESTION = 'start';
    public const GAME_QUESTION = 'game';

    public function __construct()
    {
        $this->question = [
            [
                'text' => '', // Вопрос
                'variants' => [''], // Возможные варианты ответа
                'success' => '' // Правильный ответ
            ]
        ];
        parent::__construct();
    }

    /**
     * Получаем вопрос и отображаем его пользователю
     *
     * @param int $id Идентификатор записи
     */
    protected function setQuestionText($id)
    {
        if (!isset($this->question[$id])) {
            $id = 0;
        }
        $this->userData['question_id'] = $id;
        $this->text .= $this->question[$id]['text'];
        $this->buttons->btn = $this->question[$id]['variants'];
        array_shift($this->buttons->btn); // Для интереса перемешиваем кнопки
    }

    /**
     * Проверяем правильно ответил пользователь или нет
     *
     * @param string $text пользовательский ответ
     * @param int $questionId номер вопроса
     */
    protected function isSuccess($text, $questionId)
    {
        if (Text::isSayText($this->question[$questionId]['success'], $text)) {
            $successTexts = [
                "Совершенно верно!\n"
            ];
            $this->text = Text::getText($successTexts);
            $this->tts = $text . MM\bot\components\sound\types\AlisaSound::S_AUDIO_GAME_WIN;
            $questionId++;
        } else {
            $failTexts = [
                "Ты ошибся... Попробуй ещё раз!\n"
            ];
            $this->tts = $text . MM\bot\components\sound\types\AlisaSound::S_AUDIO_GAME_LOSS;
            $this->text = Text::getText($failTexts);
        }
        $this->setQuestionText($questionId);
    }

    /**
     * Начат квест
     */
    protected function quiz()
    {
        if (isset($this->userData['question_id'])) {
            $this->isSuccess($this->userCommand, $this->userData['question_id']);
        } else {
            $this->setQuestionText(0);
        }
    }

    /**
     * Отображаем пользователю текст помощи.
     */
    protected function help()
    {
        $this->text = MM\bot\core\mmApp::$params['help_text'];
    }

    /**
     * Обработка пользовательских команд.
     *
     * Если intentName === null, значит не удалось найти обрабатываемых команд в тексте.
     * В таком случе стоит смотреть либо на предыдущую команду пользователя(которая сохранена в бд).
     * Либо вернуть текст помощи.
     *
     * Обрабатываем приветствие и команду повтори.
     *
     * @param string|null $intentName Название действия.
     */
    public function action($intentName): void
    {
        switch ($intentName) {
            case WELCOME_INTENT_NAME:
                $this->userData['prevCommand'] = self::START_QUESTION;
                $this->buttons->btn = ['Да', 'Нет'];
                break;

            case 'replay':
                $this->text = "Повторяю ещё раз:\n";
                $this->setQuestionText($this->userData['question_id']);
                break;

            default:
                switch ($this->userData['prevCommand'] ?? null) {
                    case self::START_QUESTION:
                        if (Text::isSayTrue($this->userCommand)) {
                            $this->text = "Отлично!\nТогда начинаем игу!\n";
                            $this->quiz();
                            $this->userData['prevCommand'] = self::GAME_QUESTION;
                        } elseif (Text::isSayFalse($this->userCommand)) {
                            $this->text = "Хорошо...\nПоиграем в другой раз!";
                            $this->isEnd = true;
                        } else {
                            $this->text = 'Скажи, ты готов начать игру?';
                            $this->buttons->btn = ['Да', 'Нет'];
                        }
                        break;

                    case self::GAME_QUESTION:
                        $this->quiz();
                        break;

                    default:
                        $this->help();
                        break;
                }
                break;
        }
    }
}
