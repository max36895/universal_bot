<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\components\standard;


/**
 * Вспомогательный класс для работы с текстом.
 * Class Text
 * @package bot\components\standard
 */
class Text
{
    /**
     * Обрезает текст до необходимого количества символов.
     *
     * @param string|null $text Исходный текст.
     * @param int $size Максимальный размер текста.
     * @param bool $isEllipsis Если true, тогда в конце добавится троеточие. Иначе текст просто обрезается.
     * @return string
     * @api
     */
    public static function resize(?string $text, int $size = 950, bool $isEllipsis = true): string
    {
        if ($text !== null) {
            if (mb_strlen($text, 'utf-8') > $size) {
                if ($isEllipsis) {
                    $size -= 3;
                    $text = (mb_substr($text, 0, $size) . '...');
                } else {
                    $text = mb_substr($text, 0, $size);
                }
            }
        } else {
            $text = '';
        }
        return $text;
    }

    /**
     * Вернет true в том случае, если пользователь выражает согласие.
     *
     * @param string $text Пользовательский текст.
     * @return bool
     * @api
     */
    public static function isSayTrue(string $text)
    {
        $pattern = '/(\bда\b)|(\bконечно\b)|(\bсоглас[^s]+\b)|(\bподтвер[^s]+\b)/umi';
        preg_match_all($pattern, $text, $data);
        return (($data[0][0] ?? null) ? true : false);
    }

    /**
     * Вернет true в том случае, если пользователь выражает не согласие.
     *
     * @param string $text Пользовательский текст.
     * @return bool
     * @api
     */
    public static function isSayFalse(string $text)
    {
        $pattern = '/(\bнет\b)|(\bнеа\b)|(\bне\b)/umi';
        preg_match_all($pattern, $text, $data);
        return (($data[0][0] ?? null) ? true : false);
    }

    /**
     * Вернет true в том случае, если в текста выполняется необходимое условие.
     *
     * @param array|string $find Текст который ищем.
     * @param string $text Исходный текст, в котором осуществляется поиск.
     * @param bool $isPattern Если true, тогда используется пользовательское регулярное выражение.
     * @return bool
     * @api
     */
    public static function isSayText($find, string $text, bool $isPattern = false): bool
    {
        $pattern = '';
        if (is_array($find)) {
            foreach ($find as $value) {
                if ($pattern) {
                    $pattern .= '|';
                }
                if ($isPattern == false) {
                    $pattern .= "(\\b{$value}(|[^\\s]+)\\b)";
                } else {
                    $pattern .= "({$value})";
                }

            }
        } else {
            if ($isPattern == false) {
                $pattern = "(\\b{$find}(|[^\\s]+)\\b)";
            } else {
                $pattern = $find;
            }
        }
        @preg_match_all('/' . $pattern . '/umi', $text, $data);
        return (($data[0][0] ?? null) ? true : false);
    }

    /**
     * Получить строку из массива или строки.
     *
     * @param string|array $str Исходная строка или массив из строк.
     * @return string
     * @api
     */
    public static function getText($str): string
    {
        if (is_array($str)) {
            return $str[rand(0, count($str) - 1)];
        }
        return $str;
    }

    /**
     * Добавляет нужное окончание в зависимости от числа.
     *
     * @param int $num - само число.
     * @param array $titles - массив из возможных вариантов. массив должен быть типа ['1 значение','2 значение','3 значение'].
     * Где:
     * 1 значение - это окончание, которое получится если последняя цифра числа 1
     * 2 значение - это окончание, которое получится если последняя цифра числа от 2 до 4
     * 3 значение - это окончание, если последняя цифра числа от 5 до 9 включая 0
     * Пример:
     * ['Яблоко','Яблока','Яблок']
     * Результат:
     * 1 Яблоко, 21 Яблоко, 3 Яблока, 9 Яблок
     *
     * @param int|null $index Свое значение из массива. Если элемента в массиве с данным индексом нет, тогда параметр опускается.
     *
     * @return mixed
     * @api
     */
    public static function getEnding(int $num, array $titles, ?int $index = null): ?string
    {
        if ($index !== null) {
            if (isset($titles[$index])) {
                return $titles[$index];
            }
        }
        if ($num < 0) {
            $num *= -1;
        }
        $cases = [2, 0, 1, 1, 1, 2];
        return $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]] ?? null;
    }

    /**
     * Проверка текста на сходство.
     * В результате вернет статус схожести, а также текст и ключ в массиве.
     *
     * Если текста схожи, тогда status = true, и заполняются поля:
     * index - Если был передан массив, тогда вернется его индекс.
     * text - Текст, который оказался максимально схожим.
     * percent - Процент схожести.
     *
     * @param string $origText - оригинальный текст. С данным текстом будет производиться сравнение.
     * @param string|array $text - Текст для сравнения. можно передать массив из текстов для поиска.
     * @param int $percent - при какой процентной схожести считать что текста одинаковые.
     *
     * @return array [
     *  - 'status' => bool, Статус выполнения
     *  - 'index' => int|string, В каком тексте значение совпало, либо максимальное. При передаче строки вернет 0
     *  - 'text' => string, Текст, который совпал
     *  - 'percent' => int На сколько процентов текста похожи
     * ]
     * @api
     */
    public static function textSimilarity(string $origText, $text, int $percent = 80): array
    {
        $data = [
            'percent' => 0,
            'index' => null
        ];
        if (!is_array($text)) {
            $text = [$text];
        }
        $origText = mb_strtolower($origText);
        foreach ($text as $index => $res) {
            $res = mb_strtolower($res);
            if ($res == $origText) {
                return ['status' => true, 'index' => $index, 'text' => $res, 'percent' => 100];
            }
            $per = 0;
            similar_text($origText, $res, $per);
            if ($data['percent'] < $per) {
                $data = [
                    'percent' => $per,
                    'index' => $index
                ];
            }
        }
        if ($data['percent'] >= $percent) {
            return ['status' => true, 'index' => $data['index'], 'percent' => $data['percent'], 'text' => $text[$data['index']]];
        }
        return ['status' => false, 'index' => null, 'text' => null];
    }
}
