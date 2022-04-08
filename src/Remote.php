<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\CurlFailure;
use alkemann\h2l\util\Http;
use CurlHandle;
use Exception;

/**
 * Class Remote
 *
 * Makes http requests using cURL. uses Message for both Request and Response description
 *
 * @TODO SSL verify optional
 * @TODO Proxy support?
 * @package alkemann\h2l
 */
class Remote
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];
    /**
     * @var array<int, mixed>
     */
    private array $curl_options = [];
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     * @var CurlHandle
     */
    private CurlHandle $curl_handler;
    /**
     * @var float
     */
    private float $start = 0.0;

    /**
     * Creatues the Remote instance, only sets configurations
     *
     * @param array<int, mixed> $curl_options
     * @param array<string, mixed> $config
     */
    public function __construct(array $curl_options = [], array $config = [])
    {
        $this->config = $config;
        $this->curl_options = [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'alkemann-h2l-Remote-0.29',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
            ] + $curl_options;
    }

    /**
     * Make a HTTP GET request to `$url` and return the `Message` response
     *
     * @param string $url
     * @param array $headers
     * @return Message
     */
    public function get(string $url, array $headers = []): Message
    {
        $request = (new Message())
            ->withUrl($url)
            ->withMethod(Http::GET)
            ->withHeaders($headers);
        return $this->http($request);
    }

    /**
     * Make a HTTP POST request to `$url`, posting `$data` as a json body, and return the `Message` response
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param string $method Http::POST | Http::PUT
     * @return Message
     */
    public function postJson(string $url, array $data, array $headers = [], string $method = Http::POST): Message
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $headers['Accept'] = 'application/json';
        $data_string = json_encode($data);
        $json = is_string($data_string) ? $data_string : "";
        $headers['Content-Length'] = strlen($json);
        $request = (new Message())
            ->withUrl($url)
            ->withMethod($method)
            ->withBody($json)
            ->withHeaders($headers);
        return $this->http($request);
    }

    /**
     * Make a HTTP POST request to `$url`, posting `$data` as a "form" and return the `Message` response
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @param string $method Http::POST | Http::PUT
     * @return Message
     */
    public function postForm(string $url, array $data, array $headers = [], string $method = Http::POST): Message
    {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
        $data_string = http_build_query($data);
        $headers['Content-Length'] = strlen($data_string);
        $request = (new Message())
            ->withUrl($url)
            ->withMethod($method)
            ->withBody($data_string)
            ->withHeaders($headers);
        return $this->http($request);
    }

    /**
     * Make a HTTP DELETE request to `$url` and return the `Message` response
     *
     * @param string $url
     * @param array $headers
     * @return Message
     */
    public function delete(string $url, array $headers = []): Message
    {
        $request = (new Message())
            ->withUrl($url)
            ->withMethod(Http::DELETE)
            ->withHeaders($headers);
        return $this->http($request);
    }

    /**
     * Make a HTTP request as specified by `$message` and return the `Message` response
     *
     * @param Message $message
     * @return Message
     */
    public function http(Message $message): Message
    {
        $this->createCurlHandlerFromRequest($message);
        $content = $this->execute_curl($message);
        $meta = curl_getinfo($this->curl_handler);
        $header_size = curl_getinfo($this->curl_handler, CURLINFO_HEADER_SIZE);
        $header = substr($content, 0, $header_size);
        $headers = $this->extractHeaders($header);
        $content = substr($content, $header_size);
        curl_close($this->curl_handler);
        unset($this->curl_handler);
        $meta['latency'] = microtime(true) - $this->start;
        return (new Message())
            ->withUrl($message->url())
            ->withMethod($message->method())
            ->withBody($content)
            ->withCode($meta['http_code'])
            ->withHeaders($headers)
            ->withMeta($meta)
        ;
    }

    /**
     * @param Message $request
     * @throws Exception if CurlInit fails
     */
    private function createCurlHandlerFromRequest(Message $request): void
    {
        $this->start = microtime(true);

        $handle = curl_init();

        if ($handle == false) {
            throw new Exception("Curl Init failed!");
        }

        $this->curl_handler = $handle;

        $options = $this->curl_options;
        $options += [
            CURLOPT_URL => $request->url(),
            CURLOPT_CUSTOMREQUEST => $request->method(),
        ];

        if (!isset($options[CURLOPT_HTTPHEADER])) {
            $options[CURLOPT_HTTPHEADER] = [];
        }
        $body = $request->body();
        if (empty($body) === false) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        foreach ($request->headers() as $header_name => $header_value) {
            $options[CURLOPT_HTTPHEADER][] = "{$header_name}: {$header_value}";
        }

        $headers_to_set_to_blank_if_not_set = [
            'Content-Type',
            'Expect',
            'Accept',
            'Accept-Encoding'
        ];
        foreach ($headers_to_set_to_blank_if_not_set as $name) {
            if ($request->header($name) === null) {
                $options[CURLOPT_HTTPHEADER][] = "{$name}:";
            }
        }

        curl_setopt_array($this->curl_handler, $options);
    }

    /**
     * @param Message $request
     * @return string
     * @throws CurlFailure if curl_exec returns false or throws an Exception
     */
    private function execute_curl(Message $request): string
    {
        try {
            $content = curl_exec($this->curl_handler);
            if ($content === false || is_string($content) === false) {
                throw new CurlFailure(curl_error($this->curl_handler), curl_errno($this->curl_handler));
            }
            return $content;
        } catch (Exception $e) {
            Log::error("CURL exception : " . get_class($e) . " : " . $e->getMessage());

            $curl_failure = new CurlFailure($e->getMessage(), (int) $e->getCode(), $e);
            $latency = microtime(true) - $this->start;
            $info = curl_getinfo($this->curl_handler);
            $curl_failure->setContext(compact('request', 'latency', 'info'));

            curl_close($this->curl_handler);
            unset($this->curl_handler);

            throw $curl_failure;
        }
    }

    /**
     * @param string $header
     * @return array
     */
    private function extractHeaders(string $header): array
    {
        $parts = explode("\n", $header);
        $result = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            if (strpos($part, ': ') !== false) {
                list($key, $value) = explode(": ", $part);
                $result[$key] = trim($value);
                continue;
            }

            $regex = '#^HTTP/(\d\.\d) (\d{3})(.*)#';
            if (preg_match($regex, $part, $matches)) {
                if (!empty($result)) {
                    $result = ['redirected' => $result];
                }

                $result['Http-Version'] = $matches[1];
                $result['Http-Code'] = $matches[2];
                $result['Http-Message'] = $matches[3] ? trim($matches[3]) : '';
            }
        }
        return $result;
    }
}
