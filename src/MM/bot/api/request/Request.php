<?php
/**
 * Универсальное приложение по созданию навыков и ботов.
 * @version 1.0
 * @author Maxim-M maximco36895@yandex.ru
 */

namespace MM\bot\api\request;

use CURLFile;

/**
 * Class Request
 *
 * Класс для отправки curl запросов на необходимый url.
 * Поддерживаются различные заголовки, а также есть возможность отправки файлов.
 *
 * @package bot\api\request
 */
class Request
{
    public const HEADER_RSS_XML = 'Content-Type: application/rss+xml';
    public const HEADER_GZIP = 'Content-Encoding: gzip';
    public const HEADER_AP_JSON = 'Content-Type: application/json';
    public const HEADER_AP_XML = 'Content-Type: application/xml';
    public const HEADER_FORM_DATA = 'Content-Type: multipart/form-data';

    /**
     * Адрес, на который будет отправляться запрос.
     * @var string $url Адрес, на который будет отправляться запрос.
     */
    public $url;
    /**
     * Get параметры запроса.
     * @var string|array $get Get параметры запроса.
     */
    public $get;
    /**
     * Post параметры запроса.
     * @var string|array $post Post параметры запроса.
     */
    public $post;
    /**
     * Отправляемые заголовки.
     * @var string|array $header Отправляемые заголовки.
     */
    public $header;
    /**
     * Прикрепленные файла (url, путь к файлу на сервере либо содержимое файла).
     * @var string $attach Прикрепленные файла (url, путь к файлу на сервере либо содержимое файла).
     */
    public $attach;
    /**
     * True если передается содержимое файла. По умолчанию: false.
     * @var bool $isAttachContent True если передается содержимое файла. По умолчанию: false.
     */
    public $isAttachContent;
    /**
     * Название параметра при отправке файла (По умолчанию file).
     * @var string $attachName Название параметра при отправке файла (По умолчанию file).
     */
    public $attachName;
    /**
     * Кастомный (Пользовательский) заголовок (DELETE и тд.).
     * @var string $customRequest Кастомный (Пользовательский) заголовок (DELETE и тд.).
     */
    public $customRequest;
    /**
     * Максимально время, за которое должен быть получен ответ. В мсек.
     * @var int|null $maxTimeQuery Максимально время, за которое должен быть получен ответ. В мсек.
     */
    public $maxTimeQuery;
    /**
     * True, если полученный ответ нужно преобразовать как json. По умолчанию true.
     * @var bool $isConvertJson True, если полученный ответ нужно преобразовать как json. По умолчанию true.
     */
    public $isConvertJson;

    /**
     * Ошибки при выполнении запросов.
     * @var string $error Ошибки при выполнении запросов.
     */
    private $error;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->url = null;
        $this->get = null;
        $this->post = null;
        $this->header = null;
        $this->attach = null;
        $this->isAttachContent = false;
        $this->attachName = 'file';
        $this->customRequest = null;
        $this->maxTimeQuery = null;
        $this->isConvertJson = true;
        $this->error = '';
    }

    /**
     * Возвращает текст с ошибкой, которая произошла при выполнении запроса.
     *
     * @return string
     * @api
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Запуск отправки curl запроса.
     * В случае успеха возвращает содержимое запроса, в противном случае null.
     *
     * @return mixed
     */
    private function run()
    {
        if ($this->url) {
            $curl = curl_init();
            $url = $this->url;
            if ($this->get) {
                $url .= '?' . http_build_query($this->get);
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            if ($this->maxTimeQuery) {
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $this->maxTimeQuery);
                curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->maxTimeQuery);
            }

            $post = [];
            if ($this->attach) {
                if (is_file($this->attach)) {
                    $post = array_merge($post, [$this->attachName => (class_exists('CURLFile', false)) ?
                        new CURLFile($this->attach) : '@' . $this->attach]);
                } else {
                    $this->error = 'Не удалось найти файл: ' . $this->attach;
                    return null;
                }
            }
            if ($this->post) {
                if (!is_array($this->post)) {
                    $this->post = [$this->post];
                }
                $post = array_merge($post, $this->post);
            }
            if (count($post)) {
                //$post = json_encode($post);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            }

            if ($this->header) {
                if (!is_array($this->header)) {
                    $this->header = [$this->header];
                }
                curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
            }

            if ($this->customRequest) {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->customRequest);
            }

            $data = curl_exec($curl);
            curl_close($curl);
            if ($data && $this->isConvertJson) {
                return json_decode($data, true);
            }
            return $data;
        } else {
            $this->error = 'Не указан url!';
        }
        return null;
    }

    /**
     * Отправка запрос.
     * Возвращает массив. В случае успеха свойство 'status' = true.
     *
     * @param string|null $url Адрес, на который отправляется запрос.
     * @return array
     * [
     *  - bool status Статус выполнения запроса.
     *  - mixed data Данные полученные при выполнении запроса.
     * ]
     * @api
     */
    public function send(?string $url = null): array
    {
        if ($url) {
            $this->url = $url;
        }

        $this->error = null;
        $data = $this->run();
        if ($this->error) {
            return ['status' => false, 'err' => $this->error];
        }
        return ['status' => true, 'data' => $data];
    }
}
