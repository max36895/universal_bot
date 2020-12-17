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
 * Поддерживаются различные заголовки, а также присутствует возможность отправки файлов.
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
     * Адрес, на который отправляется запрос.
     * @var string $url
     */
    public $url;
    /**
     * Get параметры запроса.
     * @var string|array $get
     */
    public $get;
    /**
     * Post параметры запроса.
     * @var string|array $post
     */
    public $post;
    /**
     * Отправляемые заголовки.
     * @var string|array $header
     */
    public $header;
    /**
     * Прикрепленны файл (url, путь к файлу на сервере либо содержимое файла).
     * @var string $attach
     */
    public $attach;
    /**
     * Тип передаваемого файла.
     * True если передается содержимое файла. По умолчанию: false.
     * @var bool $isAttachContent
     */
    public $isAttachContent;
    /**
     * Название параметра при отправке файла (По умолчанию file).
     * @var string $attachName
     */
    public $attachName;
    /**
     * Кастомный (Пользовательский) заголовок (DELETE и тд.).
     * @var string $customRequest
     */
    public $customRequest;
    /**
     * Максимально время, за которое должены получить ответ. В мсек.
     * @var int|null $maxTimeQuery
     */
    public $maxTimeQuery;
    /**
     * Формат ответа.
     * True, если полученный ответ нужно преобразовать как json. По умолчанию true.
     * @var bool $isConvertJson
     */
    public $isConvertJson;

    /**
     * Ошибки при выполнении запросов.
     * @var string $error
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
     * Возвращает текст с ошибкой, произошедшей при выполнении запроса.
     *
     * @return string
     * @api
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Начинаем отправку curl запроса.
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
     * Отправка запроса.
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
