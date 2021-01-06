<?php
/**
 * Created by u_bot
 * Date: {{date}}
 * Time: {{time}}
 */

class __className__Controller extends MM\bot\controller\BotController
{
    /**
     * Обработка пользовательских команд.
     *
     * Если intentName === null, значит не удалось найти обрабатываемых команд в тексте.
     * В таком случе стоит смотреть либо на предыдущую команду пользователя(которая сохранена в бд).
     * Либо вернуть текст помощи.
     *
     * @param string|null $intentName Название действия.
     */
    public function action(?string $intentName): void
    {
        // TODO: Implement action() method.
    }
}
