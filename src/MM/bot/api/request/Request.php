<?php
/**
 * Created by PhpStorm.
 * User: max18
 * Date: 06.03.2020
 * Time: 13:19
 */

namespace MM\bot\api\request;

use CURLFile;

/**
 * Class Request
 *
 * Класс для отправки curl запросов на необходимый url.
 *
 * @package bot\core\api\request
 *
 * @property string $url: Адрес, на который отправляется запрос
 * @property string|array $get: Get параметры запроса
 * @property string|array $post: Post параметры запроса
 * @property string|array $header: Отправляемые заголовки
 * @property string $attach: Прикрепленные файла (url, путь к файлу на сервере либо содержимое файла)
 * @property bool $isAttachContent: True если передается содержимое файла. По умолчанию: false
 * @property string $attachName: Название параметра при отправке файла (По умолчанию file)
 * @property string $customRequest: Кастомный(Пользовательский) заголовок (DELETE и тд.)
 * @property string $maxTimeQuery: Максимально время ответа в мсек.
 * @property bool $isConvertJson: True, если полученный ответ нужно преобразовать как json.
 */
class Request
{
    public const HEADER_RSS_XML = 'Content-Type: application/rss+xml';
    public const HEADER_GZIP = 'Content-Encoding: gzip';
    public const HEADER_AP_JSON = 'Content-Type: application/json';
    public const HEADER_AP_XML = 'Content-Type: application/xml';
    public const HEADER_FORM_DATA = 'Content-Type: multipart/form-data';

    public $url;
    public $get;
    public $post;
    public $header;
    public $attach;
    public $isAttachContent;
    public $attachName;
    public $customRequest;
    public $maxTimeQuery;
    public $isConvertJson;

    /**
     * @var string $error : Ошибки при выполнении запроса.
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
     * Получить текст ошибки.
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Отправка curl запроса.
     * В случае успеха возвращает массив или содержимое запроса, в противном случае null
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
            if ($data ?? $this->isConvertJson) {
                return json_decode($data, true);
            }
            return $data;
        } else {
            $this->error = 'Не указан url!';
        }
        return null;
    }

    /**
     * Отправляет запрос.
     * Возвращает массив. В случае успеха свойство 'status' = true
     *
     * @param string|null $url : Адрес, на который отправляется запрос
     * @return array
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
