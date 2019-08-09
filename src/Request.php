<?php

namespace alkemann\h2l;

use alkemann\h2l\util\Http;

/**
 * Class Request
 *
 * @TODO : $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
 * @package alkemann\h2l
 */
class Request extends Message
{
    protected $session = null;
    protected $parameters = [];
    protected $request = [];
    protected $server = [];
    protected $get = [];
    protected $post = [];
    protected $route = null;
    protected $content_type = ''; // Type of the REQUEST BODY, not response
    protected $accept_type = Http::CONTENT_HTML;
    protected $page_vars = [];

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

    public function fullUrl(?string $path = null): string
    {
        $path = $path ?? $this->url();
        return
            ($this->getServerParam('REQUEST_SCHEME') ?? 'http') . '://' .
            $this->getServerParam('HTTP_HOST') . $path;
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
        $new->method = $server['REQUEST_METHOD'] ?? Http::GET;
        $new->headers = Http::getRequestHeadersFromServerArray($server);
        return $new;
    }

    private function setContentTypeFromServerParams(string $content_type): void
    {
        $known_content_types = [
            Http::CONTENT_JSON,
            Http::CONTENT_XML,
            Http::CONTENT_TEXT_XML,
            Http::CONTENT_FORM,
            Http::CONTENT_TEXT,
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
            Http::CONTENT_JSON,
            Http::CONTENT_HTML,
            Http::CONTENT_XML,
            Http::CONTENT_TEXT_XML,
            Http::CONTENT_TEXT,
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

    public function getServerParams(): array
    {
        return $this->server;
    }

    public function getServerParam(string $name): ?string
    {
        return $this->server[$name] ?? null;
    }

    public function withPostData(array $post): Request
    {
        $new = clone $this;
        $new->post = $post;
        return $new;
    }

    public function getPostData(): array
    {
        return $this->post;
    }

    public function withGetData(array $get): Request
    {
        $new = clone $this;
        $new->get = $get;
        return $new;
    }

    public function getGetData(): array
    {
        return $this->get;
    }

    public function query(): array
    {
        return $this->get;
    }

    public function withUrlParams(array $parameters): Request
    {
        $new = clone $this;
        $new->parameters = $parameters;
        return $new;
    }

    public function getUrlParams(): array
    {
        return $this->parameters;
    }

    public function withRoute(interfaces\Route $route): Request
    {
        $new = clone $this;
        $new->route = $route;
        $new->parameters = $route->parameters();
        return $new;
    }

    public function route(): ?interfaces\Route
    {
        return $this->route;
    }

    public function withSession(interfaces\Session $session): Request
    {
        $new = clone $this;
        $new->session = $session;
        return $new;
    }

    public function pageVars(): array
    {
        return $this->page_vars;
    }

    public function withPageVars(array $vars): Request
    {
        $new = clone $this;
        $new->page_vars = $vars;
        return $new;
    }

    /**
     * Returns the session var at $key or the Session object
     *
     * First call to this method will initiate the session
     *
     * @param string $key Dot notation for deeper values, i.e. `user.email`
     * @return mixed|interfaces\Session
     */
    public function session(?string $key = null)
    {
        if (is_null($key)) {
            $this->session->startIfNotStarted();
            return $this->session;
        }
        return $this->session->get($key);
    }

    /**
     * Redirect NOW the request to $url
     *
     * @codeCoverageIgnore
     * @param $url
     */
    public function redirect($url)
    {
        // @TODO add support for reverse route match
        header("Location: " . $url);
        exit;
    }
}
