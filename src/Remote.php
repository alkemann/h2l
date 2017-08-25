<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\CurlFailure;

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
    private $config = [];
    private $curl_options = [];
    private $curl_handler;
    // @var float
    private $start;

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

    public function get(string $url, array $headers = []): Message
    {
        $request = (new Message)
            ->withUrl($url)
            ->withMethod(Request::GET)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function postJson(string $url, array $data, array $headers = [], string $method = Request::POST): Message
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $headers['Accept'] = 'application/json';
        $data_string = json_encode($data);
        $headers['Content-Length'] = strlen($data_string);
        $request = (new Message)
            ->withUrl($url)
            ->withMethod($method)
            ->withBody($data_string)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function postForm(string $url, array $data, array $headers = [], string $method = Request::POST): Message
    {
        $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
        $data_string = http_build_query($data);
        $headers['Content-Length'] = strlen($data_string);
        $request = (new Message)
            ->withUrl($url)
            ->withMethod($method)
            ->withBody($data_string)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function delete(string $url, array $headers = []): Message
    {
        $request = (new Message)
            ->withUrl($url)
            ->withMethod(Request::DELETE)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function http(Message $request): Message
    {
        $this->createCurlHandlerFromRequest($request);
        $content = $this->execute_curl($request);
        $meta = curl_getinfo($this->curl_handler);
        $header_size = curl_getinfo($this->curl_handler, CURLINFO_HEADER_SIZE);
        $header = substr($content, 0, $header_size);
        $headers = $this->extractHeaders($header);
        $content = substr($content, $header_size);
        curl_close($this->curl_handler);
        unset($this->curl_handler);
        $meta['latency'] = microtime(true) - $this->start;
        return (new Message)
            ->withUrl($request->url())
            ->withMethod($request->method())
            ->withBody($content)
            ->withCode($meta['http_code'])
            ->withHeaders($headers)
            ->withMeta($meta)
        ;
    }

    /**
     * @param Message $request
     */
    private function createCurlHandlerFromRequest(Message $request)
    {
        $this->start = microtime(true);

        $this->curl_handler = curl_init();

        $options = $this->curl_options;
        $options += [
            CURLOPT_URL => $request->url(),
            CURLOPT_CUSTOMREQUEST => $request->method(),
        ];

        if (!isset($options[CURLOPT_HTTPHEADER])) {
            $options[CURLOPT_HTTPHEADER] = [];
        }
        $body = $request->body();
        if ($body !== null) {
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
                $conf[CURLOPT_HTTPHEADER][] = "{$name}:";
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
            if ($content === false) {
                throw new CurlFailure(curl_error($this->curl_handler), curl_errno($this->curl_handler));
            }
            return $content;
        } catch (\Exception $e) {
            Log::error("CURL exception : " . get_class($e) . " : " . $e->getMessage());

            $curl_failure = new CurlFailure($e->getMessage(), $e->getCode(), $e);
            $latency = microtime(true) - $this->start;
            $info = curl_getinfo($this->curl_handler);
            $curl_failure->setContext(compact('request', 'latency', 'info'));

            curl_close($this->curl_handler);
            unset($this->curl_handler);

            throw $curl_failure;
        }
    }

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
                if ($result) {
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
