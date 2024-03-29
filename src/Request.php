<?php declare(strict_types=1);

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
    /**
     * @var null|interfaces\Route
     */
    protected ?interfaces\Route $route = null;
    /**
     * @var null|interfaces\Session
     */
    protected ?interfaces\Session $session = null;
    /** @var array<string, mixed> */
    protected array $parameters = [];
    /** @var array */
    protected array $request = [];
    /** @var array */
    protected array $server = [];
    /** @var array */
    protected array $get = [];
    /** @var array */
    protected array $post = [];
    /** @var string */
    protected string $content_type = ''; // Type of the REQUEST BODY, not response
    /** @var string */
    protected string $accept_type = Http::CONTENT_HTML;
    /** @var array */
    protected array $page_vars = [];

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
        if ($this->contentType() == Http::CONTENT_JSON) {
            $data = $this->content();
            if (empty($this->post) && is_array($data)) {
                // Cache the json body in the post array
                $this->post = $data;
            }
            if (is_array($data) && isset($data[$name])) {
                return $data[$name];
            }
        }
        return null;
    }

    /**
     * Returns the full url (with domain) of the request, alternatively for the provided path
     *
     * Alternative usage is for creating full urls, i.e. reverse routing
     *
     * @param null|string $path
     * @return string
     */
    public function fullUrl(?string $path = null): string
    {
        $path = $path ?? $this->url();
        if ($path && $path[0] != '/') {
            $path = '/' . $path;
        }

        $scheme = $this->getServerParam('REQUEST_SCHEME') ?? 'http';
        $domain = $this->getServerParam('HTTP_HOST') ?? 'localhost';
        return $scheme . '://' . $domain . $path;
    }

    /**
     * Recreate the `Request` with specified request parameters
     *
     * @param array<string, mixed> $request_params
     * @return Request
     */
    public function withRequestParams(array $request_params): Request
    {
        $new = clone $this;
        $new->url = $request_params['url'] ?? '/';
        unset($request_params['url']);
        $new->request = $request_params;
        return $new;
    }

    /**
     * Returns the request parameters of the request
     *
     * @return array<string, mixed>
     */
    public function getRequestParams(): array
    {
        return $this->request;
    }

    /**
     * Recreates the `Request` with specified server parameters
     *
     * @param array<string, mixed> $server
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

    /**
     * Returns the header specified accept type(s) of the request
     *
     * @return string
     */
    public function acceptType(): string
    {
        return $this->accept_type;
    }

    /**
     * Returns the server parameters of the request
     *
     * @return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * Returns the server parameter value of `$name` or null if not set
     *
     * @param string $name
     * @return null|string
     */
    public function getServerParam(string $name): ?string
    {
        return $this->server[$name] ?? null;
    }

    /**
     * Recreates the `Request` with the specified post data
     *
     * @param array<string, mixed> $post
     * @return Request
     */
    public function withPostData(array $post): Request
    {
        $new = clone $this;
        $new->post = $post;
        return $new;
    }

    /**
     * Returns the post data of the request
     *
     * @return array<string, mixed>
     */
    public function getPostData(): array
    {
        return $this->post;
    }

    /**
     * Recreates the `Request` with the specified get (quary) data
     *
     * @param array<string, mixed> $get
     * @return Request
     */
    public function withGetData(array $get): Request
    {
        $new = clone $this;
        $new->get = $get;
        return $new;
    }

    /**
     * Returns the request data of the request
     *
     * @return array<string, mixed>
     */
    public function getGetData(): array
    {
        return $this->get;
    }

    /**
     * Alias of `getGetData`
     *
     * @return array<string, mixed>
     */
    public function query(): array
    {
        return $this->getGetData();
    }

    /**
     * Recreates the `Request` with the specified Url parameters
     *
     * Url parameters are extracted with dynamic routes, aka:
     * `/api/location/(?<city>\w+)/visit` the "city" part.
     *
     * @param array<string, mixed> $parameters
     * @return Request
     */
    public function withUrlParams(array $parameters): Request
    {
        $new = clone $this;
        $new->parameters = $parameters;
        return $new;
    }

    /**
     * Returns the url parameters of the request
     *
     * @return array<string, mixed>
     */
    public function getUrlParams(): array
    {
        return $this->parameters;
    }

    /**
     * Recreates the `Request` with the specified Route
     *
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
     * Returns the `Route` of the request, if set, null if not.
     *
     * @return interfaces\Route|null
     */
    public function route(): ?interfaces\Route
    {
        return $this->route;
    }

    /**
     * Recreates the `Request` with the given `Session` object
     *
     * @param interfaces\Session $session
     * @return Request
     */
    public function withSession(interfaces\Session $session): Request
    {
        $new = clone $this;
        $new->session = $session;
        return $new;
    }

    /**
     * Returns the page variables (those variables to be injected into templates) of request
     *
     * @return array<string, mixed>
     */
    public function pageVars(): array
    {
        return $this->page_vars;
    }

    /**
     * Recreates the `Request` with the given page variables
     * @param array<string, mixed> $vars
     * @return Request
     */
    public function withPageVars(array $vars): Request
    {
        $new = clone $this;
        $new->page_vars = $vars;
        return $new;
    }

    /**
     * Overwrites Message::content to grab POST data
     *
     * @return null|string|array<mixed>|\SimpleXMLElement|\DOMDocument body converted from raw format
     */
    public function content()
    {
        if ($this->contentType() === Http::CONTENT_FORM) {
            return $this->post;
        }
        return parent::content();
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
        if (is_null($this->session)) {
            throw new \Exception("No Session object in Request");
        }
        if (is_null($key)) {
            $this->session->startIfNotStarted();
            return $this->session;
        }
        return $this->session->get($key);
    }

    /**
     * Redirect NOW the request to $url
     *
     * Method includes usage of the `exit` php command
     *
     * @codeCoverageIgnore
     * @param string $url
     */
    public function redirect($url): void
    {
        // @TODO add support for reverse route match
        header("Location: " . $url);
        exit;
    }
}
