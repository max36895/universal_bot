<?php
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 07.03.2020
 * Time: 19:32
 */

namespace MM\bot\components\nlu;

use MM\bot\components\standard\Text;

/**
 * Класс для обработки естественной речи. Осуществляет поиск различных сущностей в тексте
 * Class Nlu
 * @package bot\components\nlu
 */
class Nlu
{
    /**
     * @var array $nlu
     */
    private $nlu;
    /**
     * @const string T_FIO В запросе пользователя присутствует имя
     */
    public const T_FIO = 'YANDEX.FIO';
    /**
     * @const string T_GEO В запросе пользователя присутствуют координаты(Адрес, город и тд)
     */
    public const T_GEO = 'YANDEX.GEO';
    /**
     * @const string T_DATETIME В запросе пользователя присутствует дата
     */
    public const T_DATETIME = 'YANDEX.DATETIME';
    /**
     * @const string T_NUMBER В запросе пользователя есть числа
     */
    public const T_NUMBER = 'YANDEX.NUMBER';

    // ========== Встроенные интенты =========================
    // Если в навыке есть хотя бы один интент, Яндекс.Диалоги дополнительно отправляют интенты, универсальные для большинства навыков
    /**
     * @const string T_INTENT_CONFIRM: Согласие
     */
    public const T_INTENT_CONFIRM = 'YANDEX.CONFIRM';
    /**
     * @const string T_INTENT_REJECT: Отказ
     */
    public const T_INTENT_REJECT = 'YANDEX.REJECT';
    /**
     * @const string T_INTENT_HELP: Запрос подсказки
     */
    public const T_INTENT_HELP = 'YANDEX.HELP';
    /**
     * @const string T_INTENT_REPEAT: Просьба повторить последний ответ навыка
     */
    public const T_INTENT_REPEAT = 'YANDEX.REPEAT';
    // =======================================================

    /**
     * Nlu constructor.
     */
    public function __construct()
    {
        $this->nlu = [];
    }

    /**
     * Инициализация массива с nlu
     *
     * @param $nlu : Значение для nlu. В случае с Алисой передается в запросе. Для других типов инициируется самостоятельно.
     */
    public function setNlu($nlu): void
    {
        $this->nlu = $nlu;
    }

    /**
     * Получить обработанный nlu для определенного типа
     *
     * @param string $type : Тип данных
     * @return array|null
     */
    private function getData($type): ?array
    {
        $data = null;
        foreach ($this->nlu['entities'] as $entity) {
            if (($entity['type'] ?? null) && $entity['type'] == $type) {
                if ($data === null) {
                    $data = [];
                }
                $data[] = $entity['value'];
            }
        }
        return $data;
    }

    /**
     * Получение имени текущего пользователя
     *
     * @return array|null
     *  - @var array
     *      - @var string username: Логин пользователя
     *      - @var string first_name: Имя пользователя
     *      - @var string last_name: Фамилия пользователя
     */
    public function getUserName(): ?array
    {
        if (isset($this->nlu['thisUser'])) {
            return $this->nlu['thisUser'];
        }
        return null;
    }

    /**
     * Получение ФИО из текста, как правило его сгенерировал Яндекс
     *
     * Возвращается массив типа:
     * ['status'=>bool, 'result'=>array]
     *
     * 'status' == true, если значение найдено. Иначе значений найти не удалось.
     * 'result' представляет из себя массив типа
     * [
     *  [
     *      "first_name" => Имя
     *      "patronymic_name" => Отчество
     *      "last_name" => Фамилия
     *  ]
     * ]
     *
     * @return array
     *  - @var bool status
     *  - @var array result
     *      - @var array
     *          - @var string first_name
     *          - @var string patronymic_name
     *          - @var string last_name
     */
    public function getFio(): array
    {
        $status = false;
        $fio = $this->getData(self::T_FIO);
        if ($fio) {
            $status = true;
        }
        return ['status' => $status, 'result' => $fio];
    }

    /**
     * Получение местоположение из текста, как правило его сгенерировал Яндекс
     *
     * Возвращается массив типа:
     * ['status'=>bool, 'result'=>array]
     *
     * 'status' == true, если значение найдено. Иначе значений найти не удалось.
     * 'result' представляет из себя массив типа
     * [
     *  [
     *      "country" => Страна
     *      "city" => Город
     *      "street" => Улица
     *      "house_number" => Номер дома
     *      "airport" => Название аэропорта
     *  ]
     * ]
     *
     * @return array
     *  - @var bool status
     *  - @var array result
     *      - @var array
     *          - @var string country
     *          - @var string city
     *          - @var string street
     *          - @var int house_number
     *          - @var string airport
     */
    public function getGeo(): array
    {
        $status = false;
        $geo = $this->getData(self::T_GEO);
        if ($geo) {
            $status = true;
        }
        return ['status' => $status, 'result' => $geo];
    }

    /**
     * Получение даты и времени из текста, как правило его сгенерировал Яндекс
     *
     * Возвращается массив типа:
     * ['status'=>bool, 'result'=>array]
     *
     * 'status' == true, если значение найдено. Иначе значений найти не удалось.
     * 'result' представляет из себя массив типа
     * [
     *  [
     *      "year" => Точный год
     *      "year_is_relative" => Признак того, что в поле year указано относительное количество лет;
     *      "month" => Месяц
     *      "month_is_relative" => Признак того, что в поле month указано относительное количество месяцев
     *      "day" => День
     *      "day_is_relative" => Признак того, что в поле day указано относительное количество дней
     *      "hour" => Час
     *      "hour_is_relative" => Признак того, что в поле hour указано относительное количество часов
     *      "minute" => Минута
     *      "minute_is_relative" => Признак того, что в поле minute указано относительное количество минут.
     *  ]
     * ]
     *
     * @return array
     *  - @var bool status
     *  - @var array result
     *      - @var array
     *          - @var int year
     *          - @var bool year_is_relative
     *          - @var int month
     *          - @var bool month_is_relative
     *          - @var int day
     *          - @var bool day_is_relative
     *          - @var int hour
     *          - @var bool hour_is_relative
     *          - @var int minute
     *          - @var bool minute_is_relative
     */
    public function getDateTime(): array
    {
        $status = false;
        $dataTime = $this->getData(self::T_DATETIME);
        if ($dataTime) {
            $status = true;
        }
        return ['status' => $status, 'result' => $dataTime];
    }

    /**
     * Получение числа в текста, как правило его сгенерировал Яндекс
     *
     * Возвращается массив типа:
     * ['status'=>bool,'result'=>array]
     *
     * 'status' == true, если значение найдено. Иначе значений найти не удалось.
     * 'result' представляет из себя массив типа
     * [
     *  [
     *      "integer" => Целое число
     *      "float" => Десятичная дробь
     *  ]
     * ]
     *
     * @return array
     *  - @var bool status
     *  - @var array result
     *      - @var array
     *          - @var int integer
     *          or
     *          - @var float float
     */
    public function getNumber(): array
    {
        $status = false;
        $number = $this->getData(self::T_NUMBER);
        if ($number) {
            $status = true;
        }
        return ['status' => $status, 'result' => $number];
    }

    /**
     * Вернет true, если пользователь согласен
     *
     * @param string $userCommand : Фраза пользователя. Если нет совпадения по интенту, то поиск согласия идет по тексту
     * @return bool
     */
    public function isIntentConfirm(string $userCommand = ''): bool
    {
        $result = ($this->getIntent(self::T_INTENT_CONFIRM) !== null);
        if (!$result && $userCommand) {
            return Text::isSayTrue($userCommand);
        }
        return $result;
    }

    /**
     * Вернет true, если пользователь не согласен
     *
     * @param string $userCommand : Фраза пользователя. Если нет совпадения по интенту, то поиск не согласия идет по тексту
     * @return bool
     */
    public function isIntentReject(string $userCommand = ''): bool
    {
        $result = ($this->getIntent(self::T_INTENT_REJECT) !== null);
        if (!$result && $userCommand) {
            return Text::isSayFalse($userCommand);
        }
        return $result;
    }

    /**
     * Вернет true, если пользователь просит помощи
     *
     * @return bool
     */
    public function isIntentHelp(): bool
    {
        return ($this->getIntent(self::T_INTENT_HELP) !== null);
    }

    /**
     * Вернет true, если пользователь просит повторить последний ответ навыка
     *
     * @return bool
     */
    public function isIntentRepeat(): bool
    {
        return ($this->getIntent(self::T_INTENT_REPEAT) !== null);
    }

    /**
     * Получение всех intents, как правило получены от Алисы. Все интенты сгенерированы в консоли разработчика
     *
     * @return array|null
     */
    public function getIntents()
    {
        return $this->nlu['intents'] ?? null;
    }

    /**
     * Получение пользовательских интентов. (Актуально для Алисы).
     * В случае успеха вернет массив типа:
     * [['slots'=>array]]
     * Slots зависит от переменных внутри slots в консоли разработчика(https://dialogs.yandex.ru/developer/skills/<skill_id>/draft/settings/intents)
     * И включает себя:
     *  - type: Тип (YANDEX.STRING)
     *  - value: Значение
     *
     * @param string $intentName : Название intent`а
     * @return array|null
     *  - @var array
     *      - @var array slots
     *          - @var string type
     *          - @var array value
     */
    public function getIntent($intentName): ?array
    {
        $intents = $this->getIntents();
        if ($intents) {
            return $intents[$intentName] ?? null;
            /*foreach ($intents as $name => $intent) {
                if ($intentName == $name) {
                    if ($data === null) {
                        $data = [];
                    }
                    $data[] = $intent;
                }
            }*/
        }
        return null;
    }

    /**
     * Получение всех ссылок в тексте
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query : Пользовательский запрос
     * @return array
     *  - @var bool status
     *  - @var array result
     */
    public function getLink(string $query): array
    {
        $pattern = "/((http|s:\\/\\/)[^( |\\n)]+)/umi";
        preg_match_all($pattern, $query, $link);
        if (isset($link[0][0])) {
            return ['status' => true, 'result' => $link[0]];
        }
        return ['status' => false, 'result' => null];
    }

    /**
     * Получение всех номеров телефона в тексте
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query : Пользовательский запрос
     * @return array
     *  - @var bool status
     *  - @var array result
     */
    public function getPhone(string $query): array
    {
        $pattern = "/([\\d\\-\\(\\) ]{4,}\\d)|((?:\\+|\\d)[\\d\\-\\(\\) ]{9,}\\d)/umi";
        preg_match_all($pattern, $query, $phone);
        if (isset($phone[0][0])) {
            return ['status' => true, 'result' => $phone[0]];
        }
        return ['status' => false, 'result' => null];
    }

    /**
     * Получение всех e-mails в тексте
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query : Пользовательский запрос
     * @return array
     *  - @var bool status
     *  - @var array result
     */
    public function getEMail(string $query): array
    {
        $pattern = "/([^@^\\s]+\\@[^\\.^\\s]+\\.\\S{1,})/umi";
        preg_match_all($pattern, $query, $mail);
        if (isset($mail[0][0])) {
            return ['status' => true, 'result' => $mail[0]];
        }
        return ['status' => false, 'result' => null];
    }
}
