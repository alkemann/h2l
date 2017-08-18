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

    public function __construct(array $config = [])
    {
        $this->config = $config + [
                // defaults
            ];
    }

    public function get(string $url, array $headers = []): Message
    {
        $request = (new Message)
            ->withType(Message::REQUEST)
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
            ->withType(Message::REQUEST)
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
            ->withType(Message::REQUEST)
            ->withUrl($url)
            ->withMethod($method)
            ->withBody($data_string)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function delete(string $url, array $headers = []): Message
    {
        $request = (new Message)
            ->withType(Message::REQUEST)
            ->withUrl($url)
            ->withMethod(Request::DELETE)
            ->withHeaders($headers);
        return $this->http($request);
    }

    public function http(Message $request): Message
    {
        $start = microtime(true);

        $curl_handler = $this->createCurlHandlerFromRequest($request);

        $meta = [];
        $headers = [];

        try {
            $content = curl_exec($curl_handler);
            if ($content === false) {
                throw new CurlFailure(curl_error($curl_handler), curl_errno($curl_handler));
            }
        } catch (\Exception $e) {
            Log::error("CURL exception : " . get_class($e) . " : " . $e->getMessage());

            $curl_failure = new CurlFailure($e->getMessage(), $e->getCode(), $e);
            $latency = microtime(true) - $start;
            $info = curl_getinfo($curl_handler);
            $curl_failure->setContext(compact('request', 'latency', 'info'));

            curl_close($curl_handler);
            unset($curl_handler);

            throw $curl_failure;
        }

        $meta['latency'] = microtime(true) - $start;
        $meta['info'] = curl_getinfo($curl_handler);
        $code = $meta['info']['http_code'];

        $header_size = curl_getinfo($curl_handler, CURLINFO_HEADER_SIZE);
        $header = substr($content, 0, $header_size);
        if ($header) {
            $headers = $this->extractHeaders($header);
        }
        $content = substr($content, $header_size);

        // Curl handler no longer needed, let's close it

        curl_close($curl_handler);
        unset($curl_handler);

        return (new Message)
            ->withType(Message::RESPONSE)
            ->withUrl($request->url())
            ->withMethod($request->method())
            ->withBody($content)
            ->withCode($code)
            ->withHeaders($headers)
            ->withMeta($meta);
    }

    /**
     * return resource a hurl handler
     */
    private function createCurlHandlerFromRequest(Message $request)
    {
        $curl_handler = curl_init();

        $options = $this->config['curl'] ?? [];
        $options += [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_URL => $request->url(),
            CURLOPT_USERAGENT => 'alkemann\h2l\Remote',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => $request->method(),
        ];

        if (!isset($options[CURLOPT_HTTPHEADER])) {
            $options[CURLOPT_HTTPHEADER] = [];
        }
        $body = $request->body();
        if ($body) {
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

        curl_setopt_array($curl_handler, $options);

        return $curl_handler;
    }

    private function extractHeaders(string $header): array
    {
        $parts = explode("\n", $header);
        $result = [];
        foreach ($parts as $part) {
            if (strpos($part, ': ') === false) {
                if (substr($part, 0, 4) === 'HTTP') {
                    if ($result) {
                        $prev = $result;
                        $result = [];
                        $result['redirects'][] = $prev;
                    }

                    $regex = '#^HTTP/(\d\.\d) (\d{3})(.*)#';
                    if (preg_match($regex, $part, $matches)) {
                        $result['Http-Version'] = $matches[1];
                        $result['Http-Code'] = $matches[2];
                        $result['Http-Message'] = $matches[3] ? trim($matches[3]) : '';
                    }
                }
            } else {
                list($key, $value) = explode(": ", $part);
                $result[$key] = trim($value);
            }
        }
        return $result;
    }
}
