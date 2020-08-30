<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

class GameController extends MM\bot\controller\BotController
{
    private function getExample()
    {
        $value1 = rand(0, 20);
        $value2 = rand(0, 20);
        if (rand(0, 1)) {
            return [
                'example' => "$value1 + $value2 = ?",
                'result' => $value1 + $value2
            ];
        } else {
            if ($value1 < $value2) {
                return [
                    'example' => "$value2 - $value1 = ?",
                    'result' => $value2 - $value1
                ];
            } else {
                return [
                    'example' => "$value1 - $value2 = ?",
                    'result' => $value1 - $value2
                ];
            }
        }
    }

    protected function game()
    {
        if ($this->userData['example']) {
            if ($this->userData['result'] == $this->userCommand) {
                $this->text = "Молодец! Это правильный ответ! Сколько будет: \n";
                $this->userData = $this->getExample();
            } else {
                $this->text = "Не совсем... Давай ещё раз!\n";
            }
        } else {
            $this->text = "Сколько будет: \n";
            $this->userData = $this->getExample();
        }
        $this->userData['isGame'] = true;
        $this->text .= $this->userData['example'];
    }

    public function action($intentName): void
    {
        switch ($intentName) {
            case WELCOME_INTENT_NAME:
                $this->text = 'Привет! Давай поиграем в математику! 
                Чтобы начать игру скажи играть.';
                $this->buttons = ['Играть'];
                break;

            case HELP_INTENT_NAME:
                $this->text = 'Это простая игра в математику!';
                break;

            case 'replay':
                if ($this->userData['example']) {
                    $this->text = 'Повторяю твой пример: 
                    ' . $this->userData['example'];
                } else {
                    $this->text = 'Начни игру!';
                    $this->buttons = ['Начать игру'];
                }
                break;

            case 'game':
                $this->game();
                break;

            case 'by':
                $this->text = 'Пока пока!';
                $this->isEnd = true;
                break;

            default:
                if (!($this->userData['isGame'] ?? false)) {
                    $this->text = 'Извини, я тебя не понимаю... 
                    Если хочешь поиграть, скажи играть';
                    $this->buttons = ['Играть'];
                } else {
                    $this->game();
                }
                break;
        }
    }
}
