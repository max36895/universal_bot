Универсальное приложение для создания навыков и ботов
=====================================================
<b>Версия 1.0</b>

Документация
------------
Документация: [https://www.maxim-m.ru/bot/documentation/index.html](https://www.maxim-m.ru/bot/documentation/index.html). Получить информации о работе приложения можно в телеграм канале [MM](https://t.me/joinchat/AAAAAFM8AcuniLTwBLuNsw) или группе [MM](https://t.me/mm_universal_bot)

Описание
--------
Движок позволяет создать навык для Яндекс.Алиса, Маруси, Сбер(SmartApp), бота для vk, viber или telegram, с идентичной логикой.
Типы доступных приложений в дальнейшем будут дополняться.

При необходимости есть возможность создать приложение со свой типом бота.
Тип приложения должен быть установлен в `mmApp::$appType`, по умолчанию используется alisa.

Структура движка
----------------
| Директория |  | Описание |
|---|---|-------|
|bot|   |Директория с логикой ядра|
|  |api/|Доступ к Api сервисов. Отправка стандартных запросов на получение или отправку данных|
|  |&nbsp;&nbsp;&nbsp;&nbsp;request/|Отправка curl запросов|
|  |components/     |Основные компоненты ядра|
|  |&nbsp;&nbsp;&nbsp;&nbsp;button/     |Отображение кнопок (В виде ссылки, либо кнопки). Также здесь находится класс, хранящий состояние кнопок|
|  |&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;types/  |Классы отвечающие за отображение кнопок/сторонних клавиатур для определенного типа приложения|
|  |&nbsp;&nbsp;&nbsp;&nbsp;card/       |Отображение карточек (В основном изображения или списка из картинок)|
|  |&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;types/  |Классы отвечающие за отображение карточек для определенного типа приложения (Алиса, бот в контакте или телеграм)|
|  |&nbsp;&nbsp;&nbsp;&nbsp;image/      |Здесь находится класс, хранящий состояние картинок|
|  |&nbsp;&nbsp;&nbsp;&nbsp;nlu/        |Обработка запроса пользователя, для получения определенных инструментов(телефон, почта, дата и тд)|
|  |&nbsp;&nbsp;&nbsp;&nbsp;sound/      |Воспроизведение звуковых файлов|
|  |&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;types/  |Классы отвечающие за воспроизведение звуков для определенного типа приложения (Алиса, бот в контакте или телеграм)|
|  |&nbsp;&nbsp;&nbsp;&nbsp;standard/   |Стандартные команды, Облегчающие разработку. (Text, Navigation)|
|  |controller/     |Контроллер, отвечающий за обработку пользовательских команд. Класс переопределяется разработчиком|
|  |core/           |Ядро приложения|
|  |&nbsp;&nbsp;&nbsp;&nbsp;types/      |Типы приложений (Alisa, Vk, Telegram)|
|  |modules/        |Модули, предназначенные для обработки запросов. Запросы осуществляются либо к базе данным, либо к файлам. Зависит от значения переменной mmApp::$isSaveDb|
|  |&nbsp;&nbsp;&nbsp;&nbsp;db/         |Подключение к базе данных|
|  |template/       |Шаблоны приложений|

# Запуск
По умолчанию все данные записываются в файл. Для использования базы данных, установите переменную `mmApp::$isSaveDb` в `true`.
А также, создайте необходимые таблицы в бд.
```php
mmApp::setIsSaveDb(true);
```
Создать базу данных можно одним из способов:
1. Создать таблицы вручную;
2. Использовать консольный скрипт;

Рассмотрим каждый способ подробнее.
## Создание таблиц вручную
Необходимо создать 3 таблицы:
1. UsersData - Таблица, в которой будут храниться данные, введенные пользователем.
2. ImageTokens - Таблица с загруженными изображениями.
3. SoundTokens - Таблица с загруженными звуками.

Со следующей структурой:
### UsersData
```sql
CREATE TABLE IF NOT EXISTS `usersData` (
`userId` VARCHAR(250) COLLATE utf8_unicode_ci NOT NULL,
`meta` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
`data` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
`type` INT(3) DEFAULT 0,
PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
### ImageTokens
```sql
CREATE TABLE IF NOT EXISTS `ImageTokens` (
`imageToken` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
`path` VARCHAR(150) COLLATE utf8_unicode_ci DEFAULT NULL,
`type` INT(3) NOT NULL,
PRIMARY KEY (`imageToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
### SoundTokens
```sql
CREATE TABLE IF NOT EXISTS `SoundTokens` (
`soundToken` VARCHAR(150) COLLATE utf8_unicode_ci NOT NULL,
`path` VARCHAR(150) COLLATE utf8_unicode_ci DEFAULT NULL,
`type` INT(3) NOT NULL,
PRIMARY KEY (`soundToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

## Создание таблицы через консольный скрипт
Перед использованием, необходимо создать json файл, в котором будет расположена конфигурация для подключения к бд. 
Содержимое файла:
```json
{
  "config":{
    "db": {
         "host": "Расположение базы данных",
         "user": "Логин пользователя, для подключения к бд",
         "pass": "Пароль пользователя",
         "database": "Название базы"
    }
  }
}
```
После в корне проекта вызвать скрипт и указать в качестве 1 параметра `init-db`, а в качестве 2 путь к json файлу.
```bash
php ./MM/console/cApp init-db config.json
```

## Запуска приложения
### Логика приложения
Создайте класс отвечающий за логику приложения. Он должен быть унаследован от абстрактного класса `BotController`, и имеет следующий вид:
```php
/**
* Класс, содержащий логику приложения.
* Обязательно должен быть унаследован от класса BotController!
*/
class ExampleController extends MM\bot\controller\BotController
{
 /**
 * Метод, отвечающий за обработку пользовательских команд
 */
 public function action($intentName): void
 {
     /**
     * Какая-то логика приложения
     * ...
     */
 }
}
```

#### Основные переменные класса `BotController`
- Buttons `$buttons`: Кнопки отображаемые в приложении
- Card `$card`: Карточки отображаемые в приложении
- string `$text`: Текст, который увидит пользователь
- string `$tts`: Текст, который услышит пользователь
- Nlu `$nlu`: Обработанный [nlu](https://www.maxim-m.ru/glossary/nlu) в приложении
- Sound `$sound`: Класс звуков в приложении
- string `$userId`: Идентификатор пользователя
- string `$userToken`: Пользовательский токен. Инициализируется тогда, когда пользователь авторизован (Актуально для Алисы)
- array `$userMeta`: Meta данные пользователя
- string `$messageId`: Id сообщения (Порядковый номер сообщения), необходим для того, чтобы понять в 1 раз пишет пользователь или нет.
- string `$userCommand`: Запрос пользователь в нижнем регистре
- string `$originalUserCommand`: Оригинальный запрос пользователя
- array `$payload`: Дополнительные параметры запроса
- array `$userData`: Пользовательские данные (Хранятся в бд либо в файле. Зависит от значения переменной mmApp::$isSaveDb)
- bool `$isAuth`: Запросить авторизацию пользователя или нет (Актуально для Алисы)
- bool|null `$isAuthSuccess`: Проверка что авторизация пользователя прошла успешно (Актуально для Алисы)
- array `$state`: Пользовательское хранилище (Актуально для Алисы)
- bool `$isScreen`: Если ли экран (Актуально для Алисы)
- bool `$isEnd`: Завершение сессии (Актуально для Алисы)
- bool `$isSend`: Отправлять в конце запрос или нет. (Актуально для Vk и Telegram) False тогда, когда все запросы отправлены внутри логики приложения, и больше ничего отправлять не нужно

#### Основные методы и переменные класса `Buttons`
Класс, отвечающий за отображение кнопок различного формата. 
***Переменные класса:***
- Button[] `$buttons`: Массив с различными кнопками. 
- array `$btn`: Кнопки вида клавиатура/кнопка
  -  string: Текст, отображаемый на кнопке 
или
  - array
      - string title:    Текст, отображаемый на кнопке
      - string url:      Ссылка, по которой перейдет пользователь после нажатия на кнопку
      - string payload:  Дополнительные параметры, передаваемые при нажатии на кнопку
   
- array `$link`: Кнопки вида ссылка
  - string: Текст, отображаемый на кнопке 
или
  - array
      - string title:    Текст, отображаемый на кнопке
      - string url:      Ссылка, по которой перейдет пользователь после нажатия на кнопку
      - string payload:  Дополнительные параметры, передаваемые при нажатии на кнопку
string $type: Тип кнопок(кнопка в Алисе, кнопка в карточке Алисы, кнопка в Vk, кнопка в Telegram)

***Методы класса:*** 
`addBtn($title, ?string $url = '', $payload = '')` - Вставить кнопку в виде клавиатуры/кнопки. В случае успешной вставки вернет true 
**Передаваемые параметры:**
1. `$title` Текст на кнопке
2. `$url` Ссылка для перехода при нажатии на кнопку
3. `$payload` Произвольные данные, отправляемые при нажатии кнопки

`addLink($title, ?string $url = '', $payload = '')` - Вставить кнопку в виде ссылки. В случае успешной вставки вернет true 
**Передаваемые параметры:**
1. `$title` Текст на кнопке
2. `$url` Ссылка для перехода при нажатии на кнопку
3. `$payload` Произвольные данные, отправляемые при нажатии кнопки

`getButtons($type = null, $userButton = null)` - Возвращаем массив с кнопками для ответа пользователю 
**Передаваемые параметры:**
1. `$type` Тип приложения
2. `$userButton` Класс с пользовательскими кнопками.

#### Основные методы и переменные класса Card
Класс, отвечающий за отображение 1 изображения, либо списка. Список можно использовать как элемент навигации. 
***Переменные класса:***
- Image[] `$images`: Массив с изображениями или элементами карточки. Заполняется в методе add
- Buttons `$button`: Кнопки для карточки
- string `$title`: Заголовок для карточки
- string `$desc`: Описание карточки
- bool `$isOne`: True, если в любом случае отобразить только 1 изображение.

***Методы класса:*** 
`add(?string $image, $title, $desc = ' ', $button = null)` - Вставить элемент в карточку|список 
**Передаваемые параметры:**
1. `$image` Идентификатор или расположение изображения
2. `$title` Заголовок для изображения
3. `$desc` Описание для изображения
4. `$button` Кнопки, обрабатывающие команды при нажатии на элемент

`getCards($userCard = null)` - Получить все элементы типа карточка 
**Передаваемые параметры:**
1. `$userCard` Пользовательский класс для отображения карточки

#### Основные методы и переменные класса Sound
Класс, отвечающий за воспроизведение звуков в навыке, либо отправке голосовых сообщений. 
***Переменные класса:***
- array `$sounds`: Массив звуков

***Методы класса:***
`getSounds($text, $userSound = null)` - Получение корректно поставленных звуков в текст 
**Передаваемые параметры:**
1. `$text` Исходный текст
2. `$userSound` Пользовательский класс для обработки звуков

#### Основные методы класса Text
Статический класс, позволяющий производить манипуляции с текстом. 
`resize(string $text, int $size = 950)` - Обрезает текст до необходимого количества символов. Возвращает текст нужной длины 
**Передаваемые параметры:**
1. `$text` Исходный текст
2. `$size` Максимальный размер текста

`isSayTrue(string $text)` - Вернет true в том случае, если пользователь выразил согласие 
**Передаваемые параметры:**
1. `$text` Пользовательский текст

`isSayFalse(string $text)` - Вернет true в том случае, если пользователь выразил несогласие 
**Передаваемые параметры:**
1. `$text` Пользовательский текст

`isSayText($find, string $text, bool $isPattern = false)` - Вернет true в том случае, если в тексте выполняется необходимое условие 
**Передаваемые параметры:**
1. `$find` Текст, который ищем
2. `$text` Исходный текст, в котором осуществляется поиск
3. `$isPattern` Если true, тогда используется пользовательское регулярное выражение

`getText($str)` - Получить строку из массива или строки. 
**Передаваемые параметры:**
1. `$str` Исходная строка или массив из строк

`getEnding(int $num, array $titles, ?int $index = null)` - Добавляет нужное окончание в зависимости от числа 
**Передаваемые параметры:**
1. `$num` само число
2. `$titles` массив из возможных вариантов. массив должен быть типа ['1 значение','2 значение','3 значение'] 
Где:
  1 значение - это окончание, которое получится если последняя цифра числа 1.
  2 значение - это окончание, которое получится если последняя цифра числа от 2 до 4.
  3 значение - это окончание, если последняя цифра числа от 5 до 9 включая 0. 
Пример: 
  ['Яблоко', 'Яблока', 'Яблок'] 
Результат: 
  1 Яблоко, 21 Яблоко, 3 Яблока, 9 Яблок
 
3. `$index` Свое значение из массива. Если элемента в массиве с данным индексом нет, тогда параметр опускается.

`textSimilarity(string $origText, $text, int $percent = 80)` - Проверка текста на сходство. В результате вернет статус схожести, а также текст и ключ в массиве. Возвращает массив вида: 
```text
[
   'status' => bool, // Статус выполнения. True, если выполняется условие.
   'index' => int|string, // В каком тексте значение совпало, либо максимальное. При передаче строки вернет 0
   'text' => string, // Текст, который совпал
   'percent' => int // На сколько процентов текста похожи
]
```
**Передаваемые параметры:**
1. `$origText` Оригинальный текст. С данным текстом будет производиться сравнение
2. `$text` Текст для сравнения. можно передать массив из текстов для поиска.
3. `$percent` при какой процентной схожести считать что текста одинаковые

### Файл входа в приложение
Файл на который будет отправляться запрос от Яндекс, Vk, Telegram или любого другого ресурса.
```php
// Подключение файла с логикой
require_once __DIR__ . '/controller/LocalStorageController.php';

$bot = new MM\bot\core\Bot(); // Создание класса приложения
$bot->initTypeInGet(); // Получение типа приложения через get параметр type
$bot->initConfig([]); // Инициализация конфигурации приложения. Подключение к базе данных и прописывание путей к логам и сохраняемым данным.
$bot->initParams([]); // Инициализация параметров приложения. Обрабатываемые команды, токены и тд.
$bot->initBotController((new AuthController())); // Инициализация логики приложения
echo $bot->run(); // Запуск приложения
```

# Свой тип приложения.
Для добавления своего типа приложения, установите тип в значение `T_USER_APP`.
После чего передайте в функцию `run` класс, отвечающий за инициализацию и возврат данных. Класс должен быть унаследован от абстрактного класса `TemplateTypeModel`
## Создание своего типа приложения
### Логика нового типа приложения
Для начала необходимо создать класс, отвечающий за инициализацию и отображение результата работы.
Проще говоря в данном классе происходит получение данных, а также инициализация параметров(Метод `init()`).
После успешной обработки пользовательского запроса, класс должен подготовить ответ в требуемом формате(Метод `getContext()`).
Пример:
```php
<?php

use MM\bot\components\button\Buttons;
use MM\bot\controller\BotController;
use MM\bot\core\mmApp;
use MM\bot\core\types\TemplateTypeModel;

require_once __DIR__ . '/../Components/UserButton.php';
require_once __DIR__ . '/../Components/UserCard.php';
require_once __DIR__ . '/../Components/UserSound.php';

class UserApp extends TemplateTypeModel
{
    /**
     * Инициализация параметров
     *
     * @param null|string $content
     * @param BotController $controller
     * @return bool
     * @see TemplateTypeModel::init() Смотри тут
     */
    public function init(?string $content, BotController &$controller): bool
    {
        if ($content) {
            $content = json_decode($content, true);
            $this->controller = &$controller;
            $this->controller->requestObject = $content;
            /**
             * Инициализация основных параметров приложения
             */
            $this->controller->userCommand = $content['data']['text'];
            $this->controller->originalUserCommand = $content['data']['text'];

            $this->controller->userId = 'Идентификатор пользователя. Берется из $content';
            mmApp::$params['user_id'] = $this->controller->userId;
            return true;
        } else {
            $this->error = 'UserApp:init(): Отправлен пустой запрос!';
        }
        return false;
    }

    /**
     * Отправка ответа пользователю
     *
     * @return string
     * @see TemplateTypeModel::getContext() Смотри тут
     */
    public function getContext(): string
    {
        // Проверяем отправлять ответ пользователю или нет
        if ($this->controller->isSend) {
            /**
             * Отправляем ответ в нужном формате
             */
            $buttonClass = new UserButton();// Класс отвечающий за отображение кнопок. Должен быть унаследован от TemplateButtonTypes
            /*
             * Получение кнопок
             */
            $buttons = $this->controller->buttons->getButtons(Buttons::T_USER_APP_BUTTONS, $buttonClass);

            $cardClass = new UserCard();// Класс отвечающий за отображение карточек. Должен быть унаследован от TemplateCardTypes
            /*
             * Получить информацию о карточке
             */
            $cards = $this->controller->card->getCards($cardClass);

            $soundClass = new UserSound();// Класс отвечающий за отображение звуков. Должен быть унаследован от TemplateSoundTypes
            /*
             * Получить все звуки
             */
            $sounds = $this->controller->sound->getSounds('', $soundClass);
        }
        return 'ok';
    }
}

```
Класс отвечающий за инициализацию основных параметров приложения.
1. Устанавливается идентификатор пользователя
2. Указывается запрос пользователя(Введенные текст)
3. Другие необходимые параметры

В методе getContext происходит отображение полученного результата пользователю. Либо отправка ответа непосредственно через api.
В случае отображения данных, необходимо привести ответ в текстовый формат, который поддерживает приложение.

#### Второстепенные компоненты
Все типы приложений имеют примерно одинаковый интерфейс класса, и должны быть унаследованы от абстрактного класса.
Основные используемые компоненты:
1. Кнопки/клавиатура - Используется для навигации и отображения кнопок
2. Карточка - Используется для отображения изображений, коллекции из изображений, либо списка
3. Звуки - Воспроизводимые звуки. Голосовые сообщения, либо простой звук.

Пример для отображения кнопок/клавиатуры:
```php
<?php
use MM\bot\components\button\types\TemplateButtonTypes;

class UserButton extends TemplateButtonTypes
{
    /**
     * Получение массив с кнопками для ответа пользователю
     *
     * @return array
     */
    public function getButtons(): array
    {
        $objects = [];
        foreach ($this->buttons as $button) {
            /*
             * Заполняем массив $object нужными данными
             */
        }
        return $objects;
    }
}

```
Пример для отображения карточки:
```php
<?php

use MM\bot\components\button\Buttons;
use MM\bot\components\card\types\TemplateCardTypes;

require_once __DIR__ . '/UserButton.php';

class UserCard extends TemplateCardTypes
{
    /**
     * Получение массива для отображения карточки/изображения
     *
     * @param bool $isOne True, если отобразить только 1 картинку.
     * @return array
     */
    public function getCard(bool $isOne): array
    {
        $object = [];
        $countImage = count($this->images);
        if ($countImage > 7) {
            $countImage = 7;
        }
        $userButton = new UserButton();
        if ($countImage) {
            if ($countImage === 1 || $isOne) {
                if (!$this->images[0]->imageToken) {
                    if ($this->images[0]->imageDir) {
                        $this->images[0]->imageToken = $this->images[0]->imageDir;
                    }
                }
                if ($this->images[0]->imageToken) {
                    /*
                     * Заполняем $object необходимыми данными
                     */
                    // Получаем возможные кнопки у карточки
                    $btn = $this->images[0]->button->getButtons(Buttons::T_USER_APP_BUTTONS, $userButton);
                    if ($btn) {
                        // Добавляем кнопки к карточке
                        $object = array_merge($object, $btn[0]);
                    }
                }
            } else {
                foreach ($this->images as $image) {
                    if (!$image->imageToken) {
                        if ($image->imageDir) {
                            $image->imageToken = $image->imageDir;
                        }
                    }
                    $element = [];
                    /*
                     * Заполняем $element необходимыми данными
                     */
                    // Получаем возможные кнопки у карточки
                    $btn = $image->button->getButtons(Buttons::T_USER_APP_BUTTONS, $userButton);
                    if ($btn) {
                        // Добавляем кнопки к карточке
                        $object = array_merge($object, $btn[0]);
                    }
                    $object[] = $element;
                }
            }
        }
        return $object;
    }
}

```
Пример для воспроизведения звука:
```php
<?php

use MM\bot\api\YandexSpeechKit;
use MM\bot\components\sound\types\TemplateSoundTypes;
use MM\bot\components\standard\Text;

class UserSound extends TemplateSoundTypes
{
    /**
     * Возвращаем массив с воспроизводимыми звуками.
     * В случае если передается параметр text, то можно отправить запрос в Yandex SpeechKit, для преобразования текста в голос
     *
     * @param array $sounds Массив звуков
     * @param string $text Исходный текст
     * @return array
     */
    public function getSounds($sounds, $text = ''): array
    {
        if ($sounds && is_array($sounds)) {
            foreach ($sounds as $sound) {
                if (is_array($sound)) {
                    if (isset($sound['sounds'], $sound['key'])) {
                        $sText = Text::getText($sound['sounds']);
                        /*
                         * Сохраняем данные в массив, либо отправляем данные через запрос
                         */
                    }
                }
            }
        }
        /*
         * если есть необходимость для прочтения текста
         */
        if ($text) {
            $speechKit = new YandexSpeechKit();
            $content = $speechKit->getTts($text);
            if ($content) {
                /*
                * Сохраняем данные в массив, либо отправляем данные через запрос.
                 * п.с. В $content находится содержимое файла!
                */
            }
        }
        return [];
    }
}

```

## Старт
Для успешного старта на всех платформах необходимо настроить конфигурацию приложения.
А именно настроить подключение к бд(если есть необходимость), а также указать все необходимые авторизационные токены для корректной работы с api.
Массив с настройками и подключению к базе данных и логам выглядит следующим образом:
```php
$config = [
  /**
   * @var string: Директория, в которую будут записываться логи и ошибки выполнения
   */
  'error_log' => __DIR__ . '/../../logs',
  /**
   * @var string: Директория, в которую будут записываться json файлы
   */
  'json' => __DIR__ . '/../../json',
  /**
   * @var array: Настройка подключения к базе данных. Актуально если mmApp::$isSaveDb = true
   */
  'db' => [
      'host' => null, // Адрес расположения базы данных (localhost, https://example.com)
      'user' => null, // Имя пользователя
      'pass' => null, // Пароль пользователя
      'database' => null, // Название базы данных
  ]
];
```
Для установки конфигурации передайте данные в приложение следующим образом:
```php
$bot->initConfig($config);
```
Массив с параметрами приложения выглядит следующим образом:
```php
$param = [
  /**
   * @var string|null: Viber токен для отправки сообщений, загрузки изображений и звуков
   */
  'viber_token' => null,
  /**
   * @var array|string|null: Имя пользователя, от которого будет отправляться сообщение
   */
  'viber_sender' => null,
  /**
   * @var string|null: Telegram токен для отправки сообщений, загрузки изображений и звуков
   */
  'telegram_token' => null,

  /**
   * @var string|null: Версия Vk api. По умолчанию используется v5.103
   */
  'vk_api_version' => null,

  /**
   * @var string|null: Код для проверки корректности Vk бота. Необходим для подтверждения бота.
   */
  'vk_confirmation_token' => null,

  /**
   * @var string|null: Vk Токен для отправки сообщений, загрузки изображений и звуков
   */
  'vk_token' => null,

  /**
   * @var string|null: Яндекс Токен для загрузки изображений и звуков в навыке
   */
  'yandex_token' => null,

  /**
   * @var bool: Актуально для Алисы!
   * Использовать в качестве идентификатора пользователя Id в поле session->user.
   * Если true, то для всех пользователей, которые авторизованы в Яндекс будет использоваться один токен, а не разный.
   */
  'y_isAuthUser' => false,

  /**
   * @var string|null: Идентификатор приложения.
   * Заполняется автоматически.
   */
  'app_id' => null,

  /**
   * @var string|null: Идентификатор пользователя.
   * Заполняется автоматически.
   */
  'user_id' => null,
  /**
   * @var string: Текст приветствия
   */
  'welcome_text' => 'Текст приветствия',
  /**
   * @var string: Текст помощи
   */
  'help_text' => 'Текст помощи',

  /**
   * @var array: Обрабатываемые команды.
   *  - string name: Название команды. Используется для идентификации команд
   *  - $var array slots: Какие слова активируют команду. (Можно использовать регулярные выражения если установлено свойство is_pattern)
   *  - $var bool is_pattern: Использовать регулярное выражение или нет. По умолчанию false
   *
   * Пример intent с регулярным выражением:
   * [
   *  'name' => 'regex',
   *  'slots' => [
   *      '\b{_value_}\b', // Поиск точного совпадения. Например, если _value_ = 'привет', поиск будет осуществляться по точному совпадению. Слово "приветствую" в данном случае не будет считаться как точка срабатывания
   *      '\b{_value_}[^\s]+\b', // Поиск по точному началу. При данной опции слово "приветствую" станет точкой срабатывания
   *      '(\b{_value_}(|[^\s]+)\b)', // Поиск по точному началу или точному совпадению. (Используется по умолчанию)
   *      '\b(\d{3})\b', // Поиск всех чисел от 100 до 999.
   *      '{_value_} \d {_value_}', // Поиск по определенному условию. Например регулярное "завтра в \d концерт", тогда точкой срабатывания станет пользовательский текст, в котором есть вхождение что и в регулярном выражении, где "\d" это любое число.
   *      '{_value_}', // Поиск любого похожего текста. Похоже на strpos()
   *      '...' // Поддерживаются любые регулярные выражения. Перед использованием стоит убедиться в их корректности на сайте: (https://regex101.com/)
   *  ],
   *  'is_pattern' => true
   * ]
   */
  'intents' => [
      [
          'name' => WELCOME_INTENT_NAME, // Название команды приветствия
          'slots' => [ // Слова, на которые будет срабатывать приветствие
              'привет',
              'здравст'
          ]
      ],
      [
          'name' => HELP_INTENT_NAME, // Название команды помощи
          'slots' => [ // Слова, на которые будет срабатывать помощь
              'помощ',
              'что ты умеешь'
          ]
      ],
  ]
];
```
Для установки параметров передайте данные в приложение следующим образом:
```php
$bot->initParams($param);
```

# SSL
Для работы некоторых приложений, необходимо иметь ssl сертификат. Поэтому стоит его получить. Для этого можно воспользоваться acme.
## Install acme.sh
```bash
curl https://get.acme.sh | sh
```
## Issue and install certificate for site
```bash
acme.sh --issue -d {{domain}} -w {{domain dir}}
~/.acme.sh/acme.sh  --issue -d {{domain}} -w {{domain dir}}
```
1. domain - Название домена (example.com)
2. domain dir - Директория, в которой находится сайт

```bash
acme.sh --install-cert -d {{domain}} --key-file {{key file}} --fullchain-file {{cert file}} --reloadcmd "service nginx reload"
~/.acme.sh/acme.sh --install-cert -d {{domain}} --key-file {{key file}} --fullchain-file {{cert file}} --reloadcmd "service nginx reload"
```
1. domain - Название домена (example.com)
2. key file - Директория, в которой хранится ключ сертификата
3. cert file - Директория, в которой сохранится сертификат

## Важно!
После получения сертификата, необходимо перезапустить сервер `sudo service nginx reload`


# Ngrok
Используется для локального тестирование навыка. Актуально для Алисы
## Установка
Смотрите на сайте [ngrok](https://ngrok.com/download)
## Запуск
```bash
ngrok http --host-header=rewrite <domain>:port
```
1. domain - локальный адрес сайта. Важно сайт должен быть доступен на машине! (Прописан в hosts)
2. port - Порт для подключения. Для бесплатного аккаунта нельзя использовать 443 порт

После успешного запуска, нужно скопировать полученную ссылку с https, и вставить в консоль разработчика.

# Тестирование
Протестировать приложение можно 2 способами:
1. Через ngrok
2. Через консоль
## Тестирование через Ngroc
Для тестирование через ngrok, нужно скачать программу, и запустить её.
После полученную ссылку с https, вставить в [консоль разработчика](https://dialogs.yandex.ru/developer), и перейти на вкладку тестирования.
Данное действие актуально для Алисы.

## Тестирование в консоли
Для тестирования используется тот же код что и при запуске. С той лишь разнице, что нужно вызвать метод test вместо run.
```php
// Подключение файла с логикой
require_once __DIR__ . '/controller/ExampleController.php';

$bot = new MM\bot\core\Bot(); // Создание класса приложения
$bot->initTypeInGet(); // Получение типа приложения через get параметр type
$bot->initConfig([]); // Инициализация конфигурации приложения. Подключение к базе данных и прописывание путей к логам и сохраняемым данным.
$bot->initParams([]); // Инициализация параметров приложения. Обрабатываемые команды, токены и тд.
$bot->initBotController((new AuthController())); // Инициализация логики приложения
echo $bot->test(); // Запуск приложения
```
После запустить приложение.
```bash
php index.php
```
Откроется консоль с ботом. Для выхода из режима тестирования нужно:
1. Если навык в определенный момент ставит `isEnd` в True(Что означает завершение диалога), то нужно дойти до того места сценария, в котором диалог завершается.
2. Ввести команду exit.

Помимо ответов, можно вернуть время обработки команд.

Помощь и поддержка проекта
------
Любая помощь и поддержка приветствуется.
Если будут найдены различные ошибки или предложения по улучшению, то смело пишите на почту: maximco36895@yandex.ru