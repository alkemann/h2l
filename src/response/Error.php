<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Environment;
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\Log;
use alkemann\h2l\Message;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;

/**
 * Class Error
 *
 * @package alkemann\h2l
 */
class Error extends Response
{
    protected $content_type = 'text/html';
    protected $code = 500;
    protected $data = [];
    /**
     * @var Request
     */
    protected $request = null;
    protected $config = [];

    public function __construct(array $data = [], array $config = [])
    {
        $this->data = $data;
        foreach (['content_type', 'code', 'request'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }

        if ($this->request) {
            $this->content_type = $this->request->acceptType();
        }

        $this->config = $config + [
                'page_class' => Page::class
            ];

        $this->message = (new Message())
            ->withCode($this->code)
            ->withHeader('Content-Type', $this->content_type)
        ;
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    public function render(): string
    {
        $response = '';
        $page_class = $this->config['page_class'];
        try {
            $page_config = $this->config + [
                    'code' => $this->code,
                    'template' => $this->code == 404 ? '404' : 'error',
                    'content_type' => $this->content_type,
                    'request' => $this->request
                ];
            $data = $this->data + ['code' => $this->code];
            /**
             * @var $page Page
             */
            $page = new $page_class($data, $page_config);
            $page->isValid();

            return $page->render();
        } catch (InvalidUrl $e) {
            Log::debug("No error page made at " . $e->getMessage());
            if (Environment::get('debug')) {
                $response = "No error page made at " . $e->getMessage();
            }
        }
        $this->setHeaders();
        return $response;
    }
}
