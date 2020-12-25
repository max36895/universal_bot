<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\nlu;

use MM\bot\components\standard\Text;

/**
 * Класс отвечающий за обработку естественной речи. Осуществляет поиск различных сущностей в тексте.
 * Class Nlu
 * @package bot\components\nlu
 */
class Nlu
{
    /**
     * Массив с обработанным nlu.
     * @var array $nlu
     */
    private $nlu;
    /**
     * @const string T_FIO В запросе пользователя присутствует имя.
     */
    public const T_FIO = 'YANDEX.FIO';
    /**
     * @const string T_GEO В запросе пользователя присутствуют координаты(Адрес, город и тд).
     */
    public const T_GEO = 'YANDEX.GEO';
    /**
     * @const string T_DATETIME В запросе пользователя присутствует дата.
     */
    public const T_DATETIME = 'YANDEX.DATETIME';
    /**
     * @const string T_NUMBER В запросе пользователя есть числа.
     */
    public const T_NUMBER = 'YANDEX.NUMBER';

    /**
     * ========== Встроенные интенты =========================
     * Если в навыке есть хотя бы один интент, Яндекс.Диалоги дополнительно отправляют интенты, универсальные для большинства навыков
     */
    /**
     * @const string T_INTENT_CONFIRM: Согласие.
     */
    public const T_INTENT_CONFIRM = 'YANDEX.CONFIRM';
    /**
     * @const string T_INTENT_REJECT: Отказ.
     */
    public const T_INTENT_REJECT = 'YANDEX.REJECT';
    /**
     * @const string T_INTENT_HELP: Запрос подсказки.
     */
    public const T_INTENT_HELP = 'YANDEX.HELP';
    /**
     * @const string T_INTENT_REPEAT: Просьба повторить последний ответ навыка.
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
     * Приводим nlu в пригодный для работы вид.
     * @param array|null $nlu
     * @return array|null
     */
    protected function serializeNlu(?array $nlu):?array
    {
        // todo добавить обработку
        return $nlu;
    }

    /**
     * Проинициализировать nlu данные.
     *
     * @param array|null $nlu Значение для nlu. В случае с Алисой передается в запросе. Для других типов инициируется самостоятельно.
     * @api
     */
    public function setNlu(?array $nlu): void
    {
        $this->nlu = $this->serializeNlu($nlu);
    }

    /**
     * Получение обработанного nlu для определенного типа.
     *
     * @param string $type Тип данных.
     * @return array|null
     * @api
     */
    private function getData(string $type): ?array
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
     * Получение имени текущего пользователя.
     *
     * @return array|null
     * [
     *  [
     *      - string username: Логин пользователя.
     *      - string first_name: Имя пользователя.
     *      - string last_name: Фамилия пользователя.
     *  ]
     * ]
     * @api
     */
    public function getUserName(): ?array
    {
        if (isset($this->nlu['thisUser'])) {
            return $this->nlu['thisUser'];
        }
        return null;
    }

    /**
     * Получение ФИО.
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
     * [
     *  - bool status
     *  - array result
     *      [
     *          - string first_name
     *          - string patronymic_name
     *          - string last_name
     *      ]
     * ]
     * @api
     */
    public function getFio(): array
    {
        $fio = $this->getData(self::T_FIO);
        $status = $fio ? true : false;
        return ['status' => $status, 'result' => $fio];
    }

    /**
     * Получение местоположение.
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
     * [
     *  - bool status
     *  - array result
     *      [
     *          - string country
     *          - string city
     *          - string street
     *          - int house_number
     *          - string airport
     *      ]
     * ]
     * @api
     */
    public function getGeo(): array
    {
        $geo = $this->getData(self::T_GEO);
        $status = $geo ? true : false;
        return ['status' => $status, 'result' => $geo];
    }

    /**
     * Получение даты и времени.
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
     * [
     *  - bool status
     *  - array result
     *      [
     *          - int year
     *          - bool year_is_relative
     *          - int month
     *          - bool month_is_relative
     *          - int day
     *          - bool day_is_relative
     *          - int hour
     *          - bool hour_is_relative
     *          - int minute
     *          - bool minute_is_relative
     *      ]
     * ]
     * @api
     */
    public function getDateTime(): array
    {
        $dateTime = $this->getData(self::T_DATETIME);
        $status = $dateTime ? true : false;
        return ['status' => $status, 'result' => $dateTime];
    }

    /**
     * Получение числа.
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
     * [
     *  - bool status
     *  - array result
     *      [
     *          - int integer
     *          or
     *          - float float
     *      ]
     * ]
     * @api
     */
    public function getNumber(): array
    {
        $number = $this->getData(self::T_NUMBER);
        $status = $number ? true : false;
        return ['status' => $status, 'result' => $number];
    }

    /**
     * Вернет true, если пользователь даёт согласие.
     *
     * @param string $userCommand Фраза пользователя. Если нет совпадения по интенту, то поиск согласия идет по тексту.
     * @return bool
     * @api
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
     * Вернет true, если пользователь не даёт согласие.
     *
     * @param string $userCommand Фраза пользователя. Если нет совпадения по интенту, то поиск несогласия идет по тексту.
     * @return bool
     * @api
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
     * Вернет true, если пользователь просит помощи.
     *
     * @return bool
     * @api
     */
    public function isIntentHelp(): bool
    {
        return ($this->getIntent(self::T_INTENT_HELP) !== null);
    }

    /**
     * Вернет true, если пользователь просит повторить последний ответ навыка.
     *
     * @return bool
     * @api
     */
    public function isIntentRepeat(): bool
    {
        return ($this->getIntent(self::T_INTENT_REPEAT) !== null);
    }

    /**
     * Получение всех intents, как правило получены от Алисы. Все интенты сгенерированы в консоли разработчика.
     *
     * @return array|null
     * @api
     */
    public function getIntents(): ?array
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
     * @param string $intentName Название intent`а
     * @return array|null
     * [
     *  - array slots
     *  [
     *      - string type
     *      - array value
     *  ]
     * ]
     * @api
     */
    public function getIntent($intentName): ?array
    {
        $intents = $this->getIntents();
        if ($intents) {
            return $intents[$intentName] ?? null;
        }
        return null;
    }

    /**
     * Получение всех ссылок в тексте.
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query Пользовательский запрос.
     * @return array
     * [
     *  - bool status
     *  - array result
     * ]
     * @api
     */
    public static function getLink(string $query): array
    {
        $pattern = "/((http|s:\\/\\/)[^( |\\n)]+)/umi";
        preg_match_all($pattern, $query, $link);
        if (isset($link[0][0])) {
            return ['status' => true, 'result' => $link[0]];
        }
        return ['status' => false, 'result' => null];
    }

    /**
     * Получение всех номеров телефона в тексте.
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query Пользовательский запрос.
     * @return array
     * [
     *  - bool status
     *  - array result
     * ]
     * @api
     */
    public static function getPhone(string $query): array
    {
        $pattern = "/([\\d\\-\\(\\) ]{4,}\\d)|((?:\\+|\\d)[\\d\\-\\(\\) ]{9,}\\d)/umi";
        preg_match_all($pattern, $query, $phone);
        if (isset($phone[0][0])) {
            return ['status' => true, 'result' => $phone[0]];
        }
        return ['status' => false, 'result' => null];
    }

    /**
     * Получение всех e-mail в тексте.
     * Возвращает массив типа:
     * ['status' => bool, 'result' => array]
     *
     * @param string $query Пользовательский запрос.
     * @return array
     * [
     *  - bool status
     *  - array result
     * ]
     * @api
     */
    public static function getEMail(string $query): array
    {
        $pattern = "/([^@^\\s]+@[^\\.^\\s]+\\.\\S{1,})/umi";
        preg_match_all($pattern, $query, $mail);
        if (isset($mail[0][0])) {
            return ['status' => true, 'result' => $mail[0]];
        }
        return ['status' => false, 'result' => null];
    }
}
