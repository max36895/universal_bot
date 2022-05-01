<?php

namespace MM\bot\components\standard;

/**
 * Класс отвечающий за корректную навигацию по элементам меню.
 * Class Navigation
 * @package bot\components\standard
 */
class Navigation
{
    public const STANDARD_NEXT_TEXT = ['дальше', 'вперед'];
    public const STANDARD_OLD_TEXT = ['назад'];

    /**
     * Использование стандартных команд навигации
     * Если true, тогда используются стандартные команды.
     * @var bool $isUsedStandardText
     */
    public $isUsedStandardText;
    /**
     * Массив с возможными командами для навигации вперед.
     * @var array $nextText
     */
    public $nextText;
    /**
     * Массив с возможными командами для навигации назад.
     * @var array $oldText
     */
    public $oldText;
    /**
     * Массив элементов для обработки.
     * @var array $elements
     */
    public $elements;
    /**
     * (default 5) Максимальное количество отображаемых элементов.
     * @var int $maxVisibleElements
     */
    public $maxVisibleElements;
    /**
     * (default 0) Текущая страница. Рекомендуется получать это значение после завершения всех операция.
     * @var int $thisPage
     */
    public $thisPage;

    /**
     * Navigation constructor.
     * @param int $maxVisibleElements Максимально количество отображаемых элементов.
     */
    public function __construct(int $maxVisibleElements = 5)
    {
        $this->isUsedStandardText = true;
        $this->nextText = [];
        $this->oldText = [];
        $this->elements = [];
        $this->maxVisibleElements = $maxVisibleElements;
        $this->thisPage = 0;
    }

    /**
     * Определяет желание пользователя двигаться вперед.
     *
     * @param string $text Пользовательский запрос.
     * @return bool
     * @api
     */
    public function isNext(string $text): bool
    {
        if ($this->isUsedStandardText) {
            $nextText = array_merge($this->nextText, self::STANDARD_NEXT_TEXT);
        } else {
            $nextText = $this->nextText;
        }
        return Text::isSayText($nextText, $text);
    }

    /**
     * Определяет желание пользователя двигаться назад.
     *
     * @param string $text Пользовательский запрос.
     * @return bool
     * @api
     */
    public function isOld(string $text): bool
    {
        if ($this->isUsedStandardText) {
            $oldText = array_merge($this->oldText, self::STANDARD_OLD_TEXT);
        } else {
            $oldText = $this->oldText;
        }
        return Text::isSayText($oldText, $text);
    }

    /**
     * Валидация введенной страницы
     *
     * @param int $maxPage
     * @private
     */
    protected function validatePage(int $maxPage = null): void
    {
        if ($maxPage === null) {
            $maxPage = $this->getMaxPage();
        }
        if ($this->thisPage >= $maxPage) {
            $this->thisPage = $maxPage - 1;
        }
        if ($this->thisPage < 0) {
            $this->thisPage = 0;
        }
    }

    /**
     * Определяет желание пользователя перейти на определенную страницу.
     * В случае успешного перехода вернет true.
     *
     * @param string $text Пользовательский запрос.
     * @return bool
     * @api
     */
    public function numberPage(string $text): bool
    {
        @preg_match_all('/((-|)\d) страни/umi', $text, $data);
        if (isset($data[0][0])) {
            $this->thisPage = ((int)$data[0][0]) - 1;
            $this->validatePage();
            return true;
        }
        return false;
    }

    /**
     * Осуществляет переход на следующую страницу.
     * В случае успешного перехода вернет true.
     *
     * @param string $text Пользовательский запрос.
     * @return bool
     */
    protected function nextPage(string $text): bool
    {
        if ($this->isNext($text)) {
            $this->thisPage++;
            $this->validatePage();
            return true;
        }
        return false;
    }

    /**
     * Осуществляет переход на предыдущую страницу.
     * В случае успешного перехода вернет true.
     *
     * @param string $text Пользовательский запрос.
     * @return bool
     */
    protected function oldPage(string $text): bool
    {
        if ($this->isOld($text)) {
            $this->thisPage--;
            $this->validatePage();
            return true;
        }
        return false;
    }

    /**
     * Возвращает новый массив данных, с учетом текущего положения пользователя.
     *
     * @param array|null $elements Элемент для обработки.
     * @param string $text Пользовательский запрос.
     * @return array
     * @api
     */
    public function nav(?array $elements = null, string $text = ''): array
    {
        $showElements = [];
        if ($elements) {
            $this->elements = $elements;
        }
        $this->nextPage($text);
        $this->oldPage($text);
        $start = $this->thisPage * $this->maxVisibleElements;
        $end = $start + $this->maxVisibleElements;
        if (count($this->elements) >= $start) {
            for ($i = $start; $i < $end; $i++) {
                if (isset($this->elements[$i])) {
                    $showElements[] = $this->elements[$i];
                }
            }
        }
        return $showElements;
    }

    /**
     * Выбор определенного элемента списка на нужной странице.
     *
     * @param array|null $elements Элемент для обработки.
     * @param string $text Пользовательский запрос.
     * @param array|string|null $key Поиск элемента по ключу массива. Если null, тогда подразумевается, что передан массив из строк.
     * @param int|null $thisPage Текущая страница.
     * @return mixed
     * @api
     */
    public function selectedElement(?array $elements = null, string $text = '', $key = null, ?int $thisPage = null)
    {
        if ($thisPage !== null) {
            $this->thisPage = $thisPage;
        }
        if ($elements) {
            $this->elements = $elements;
        }
        $number = null;
        @preg_match_all('/(\d)/umi', $text, $data);
        if (isset($data[0][0])) {
            $number = $data[0][0];
        }
        $start = $this->thisPage * $this->maxVisibleElements;
        $index = 1;
        $selectElement = null;
        $maxPercent = 0;
        $end = $start + $this->maxVisibleElements;
        for ($i = $start; $i < $end; $i++) {
            if (isset($this->elements[$i])) {
                if ($index == $number) {
                    return $this->elements[$i];
                }
                if ($key === null) {
                    if (is_string($this->elements[$i])) {
                        $r = Text::textSimilarity($this->elements[$i], $text, 75);
                        if ($r['status'] && $r['percent'] > $maxPercent) {
                            $selectElement = $this->elements[$i];
                        }
                    }
                } else {
                    if (is_array($key)) {
                        foreach ($key as $k) {
                            if (isset($this->elements[$i][$k])) {
                                $r = Text::textSimilarity((string)$this->elements[$i][$k], $text, 75);
                                if ($r['status'] && $r['percent'] > $maxPercent) {
                                    $selectElement = $this->elements[$i];
                                }
                            }
                        }
                    } else {
                        if (isset($this->elements[$i][$key])) {
                            $r = Text::textSimilarity((string)$this->elements[$i][$key], $text, 75);
                            if ($r['status'] && $r['percent'] > $maxPercent) {
                                $selectElement = $this->elements[$i];
                            }
                        }
                    }
                }
                $index++;
                if ($maxPercent > 98) {
                    return $selectElement;
                }
            }
        }
        return $selectElement;
    }

    /**
     * Возвращает кнопки навигации.
     *
     * @param bool $isNumber Использование числовой навигации. Если true, тогда будут отображаться кнопки с числовой навигацией.
     * @return array
     * @api
     */
    public function getPageNav(bool $isNumber = false): array
    {
        $maxPage = $this->getMaxPage();
        $this->validatePage($maxPage);
        $buttons = [];
        if ($isNumber === false) {
            if ($this->thisPage) {
                $buttons[] = '👈 Назад';
            }
            if (($this->thisPage + 1) < $maxPage) {
                $buttons[] = 'Дальше 👉';
            }
        } else {
            $index = $this->thisPage - 2;
            if ($index < 0) {
                $index = 0;
            }
            $count = 0;
            if ($index === 1) {
                $buttons[] = '1';
            } elseif ($index) {
                $buttons[] = '1 ...';
            }
            for ($i = $index; $i < $maxPage; $i++) {
                if ($i === $this->thisPage) {
                    $thisPage = $i + 1;
                    $buttons[] = "[{$thisPage}]";
                } else {
                    $buttons[] = $i + 1;
                }
                $count++;
                if ($count > 4) {
                    if ($i === $maxPage - 2) {
                        $buttons[] = "{$maxPage}";
                    } elseif ($i < $maxPage - 2) {
                        $buttons[] = "... {$maxPage}";
                    }
                    break;
                }
            }
        }
        return $buttons;
    }

    /**
     * Возвращает информацию о текущей позиции пользователя.
     *
     * @return string
     * @api
     */
    public function getPageInfo(): string
    {
        if (!isset($this->elements[$this->thisPage * $this->maxVisibleElements]) || $this->thisPage < 0) {
            $this->thisPage = 0;
        }
        $pageInfo = ($this->thisPage + 1) . ' страница из ';
        $maxPage = $this->getMaxPage();
        if ($maxPage > 1) {
            $pageInfo .= $maxPage;
        } else {
            $pageInfo = '';
        }
        return $pageInfo;
    }

    /**
     * Возвращает максимальное количество страниц.
     *
     * @param array|null $elements Элемент для обработки.
     * @return int
     * @api
     */
    public function getMaxPage(?array $elements = null): int
    {
        if ($elements) {
            $this->elements = $elements;
        }
        if (is_array($this->elements)) {
            $countEl = count($this->elements);
            $maxPage = (int)($countEl / $this->maxVisibleElements);
            if ($countEl % $this->maxVisibleElements) {
                $maxPage++;
            }
            return $maxPage;
        }
        return 0;
    }
}
