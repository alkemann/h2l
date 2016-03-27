<?php

namespace alkemann\h2l;

class Request
{
    const GET    = 'GET';
    const PATCH  = 'PATCH';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';

    private $_request;
    private $_server;
    private $_parameters;
    private $_get;
    private $_post;
    private $_url;
    private $_method;
    private $_type = 'html';
    protected $_route;

    public function __construct(array $request = [], array $server = [], array $get = [], array $post = [])
    {
        $this->_request = $request;
        $this->_server  = $server;
        $this->_post    = $post;
        $this->_get     = $get;
        unset($this->_get['url']);
        $this->_parameters = [];

        // override html type with json
        $httaccept = $this->_server['HTTP_ACCEPT'] ?? '*/*';
        if ($httaccept !== '*/*' && strpos($this->_server['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->_type = 'json';
        }

        $this->_url     = $this->_request['url'] ?? '/';
        $this->_method  = $this->_server['REQUEST_METHOD'] ?? Request::GET;
        $this->_route   = Router::match($this->_url, $this->_method);
    }

    public function getPostBody():string { return file_get_contents('php://input'); }
    public function route():Route { return $this->_route; }
    public function url():string { return $this->_url; }
    public function method():string { return $this->_method; }
    public function type():string { return $this->_type; }

    public function param($name) {
        if (isset($this->_parameters[$name]))
            return $this->_parameters[$name];
        if (isset($this->_get[$name]))
            return $this->_get[$name];
        if (isset($this->_post[$name]))
            return $this->_post[$name];
        return null;
    }

    public function query(): array
    {
        return $this->_get;
    }

    public function response() : Response
    {
        $cb = $this->_route->callback;
        $this->_parameters = $this->_route->parameters;
        return call_user_func_array($cb, [$this]);
    }
}
