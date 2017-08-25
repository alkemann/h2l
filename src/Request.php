<?php

namespace alkemann\h2l;

/**
 * Class Request
 *
 * @TODO : $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
 * @package alkemann\h2l
 */
class Request extends Message
{
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const CONNECT = 'CONNECT';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    const PATCH = 'PATCH';

    protected $parameters = [];
    protected $request = [];
    protected $server = [];
    protected $get = [];
    protected $post = [];
    protected $route = null;
    protected $content_type = '';
    protected $accept_type = Message::CONTENT_HTML;

    /**
     * Get request parameters from url as url params, get queries or post, in that order
     *
     * @param string $name the name of the parameter
     * @return mixed|null the value or null if not set
     */
    public function param(string $name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        if (isset($this->get[$name])) {
            return $this->get[$name];
        }
        if (isset($this->post[$name])) {
            return $this->post[$name];
        }
        return null;
    }

    /**
     * @param array $request
     * @return Request
     */
    public function withRequestParams(array $request): Request
    {
        $new = clone $this;
        $new->url = $request['url'] ?? '/';
        unset($request['url']);
        $new->request = $request;
        return $new;
    }

    /**
     * @return array
     */
    public function getRequestParams(): array
    {
        return $this->request;
    }

    /**
     * @param array $server
     * @return Request
     */
    public function withServerParams(array $server): Request
    {
        $new = clone $this;
        $new->server = $server;
        $new->setContentTypeFromServerParams($server['HTTP_CONTENT_TYPE'] ?? '');
        $new->setAcceptTypeFromServerParams($server['HTTP_ACCEPT'] ?? '');
        $new->method = $server['REQUEST_METHOD'] ?? Request::GET;
        $new->headers = Util::getRequestHeadersFromServerArray($server);
        return $new;
    }

    private function setContentTypeFromServerParams(string $content_type): void
    {
        $known_content_types = [
            Message::CONTENT_JSON,
            Message::CONTENT_XML,
            Message::CONTENT_TEXT_XML,
            Message::CONTENT_FORM
        ];
        foreach ($known_content_types as $t) {
            if (strpos($content_type, $t) !== false) {
                $this->content_type = $t;
                return;
            }
        }
    }

    private function setAcceptTypeFromServerParams(string $accept_type): void
    {
        $known_accept_types = [
            Message::CONTENT_JSON,
            Message::CONTENT_HTML,
            Message::CONTENT_XML,
            Message::CONTENT_TEXT_XML,
        ];
        foreach ($known_accept_types as $t) {
            if (strpos($accept_type, $t) !== false) {
                $this->accept_type = $t;
                return;
            }
        }
    }

    public function acceptType(): string
    {
        return $this->accept_type;
    }

    /**
     * @return array
     */
    public function getServerParam(): array
    {
        return $this->server;
    }

    /**
     * @param array $post
     * @return Request
     */
    public function withPostData(array $post): Request
    {
        $new = clone $this;
        $new->post = $post;
        return $new;
    }

    /**
     * @return array
     */
    public function getPostData(): array
    {
        return $this->post;
    }

    /**
     * @param array $get
     * @return Request
     */
    public function withGetData(array $get): Request
    {
        $new = clone $this;
        $new->get = $get;
        return $new;
    }

    /**
     * @return array
     */
    public function getGetData(): array
    {
        return $this->get;
    }

    /**
     * @return array
     */
    public function query(): array
    {
        return $this->get;
    }

    /**
     * @param array $parameters
     * @return Request
     */
    public function withUrlParams(array $parameters): Request
    {
        $new = clone $this;
        $new->parameters = $parameters;
        return $new;
    }

    /**
     * @return array
     */
    public function getUrlParams(): array
    {
        return $this->parameters;
    }

    /**
     * @param interfaces\Route $route
     * @return Request
     */
    public function withRoute(interfaces\Route $route): Request
    {
        $new = clone $this;
        $new->route = $route;
        $new->parameters = $route->parameters();
        return $new;
    }

    /**
     * @return interfaces\Route|null
     */
    public function route(): ?interfaces\Route
    {
        return $this->route;
    }
}
