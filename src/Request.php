<?php

namespace alkemann\h2l;

/**
 * Class Request
 *
 * @package alkemann\h2l
 */
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

    /**
     * @var Route
     */
    protected $_route;

    /**
     * Analyze request, provided $_REQUEST, $_SERVER [, $_GET, $_POST] to identify Route
     *
     * Response type can be set from HTTP_ACCEPT header. the Route object will be set by a call
     * to Router::match
     *
     * @param array $request $_REQUEST
     * @param array $server $_SERVER
     * @param array $get $_GET
     * @param array $post $_POST
     */
    public function __construct(array $request = [], array $server = [], array $get = [], array $post = [])
    {
        $this->_request = $request;
        $this->_server  = $server;
        $this->_post    = $post;
        $this->_get     = $get;
        unset($this->_get['url']); // @TODO Use a less important keyword, as it blocks that _GET param?
        $this->_parameters = [];

        // override html type with json
        $httaccept = $this->_server['HTTP_ACCEPT'] ?? '*/*';
        if ($httaccept !== '*/*' && strpos($httaccept, 'application/json') !== false) {
            $this->_type = 'json';
        }

        $this->_url     = $this->_request['url'] ?? '/';
        $this->_method  = $this->_server['REQUEST_METHOD'] ?? Request::GET;
        $this->_route   = Router::match($this->_url, $this->_method);
    }

    /**
     * @return string the raw 'php://input' post
     */
    public function getPostBody():string { return file_get_contents('php://input'); }

    /**
     * @return Route identified for request
     */
    public function route():Route { return $this->_route; }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route):void { $this->_route = $route; }

    /**
     * @return string the requested url
     */
    public function url():string { return $this->_url; }

    /**
     * @return string Request::GET, Request::POST, Request::PATCH, Request::PATCH etc
     */
    public function method():string { return $this->_method; }

    /**
     * @return string 'html', 'json', 'xml' etc
     */
    public function type():string { return $this->_type; }

    /**
     * Get request parameters from url as url parats, get queries or post, in that order
     *
     * @param $name the name of the parameter
     * @return mixed|null the value or null if not set
     */
    public function param(string $name)
    {
        if (isset($this->_parameters[$name]))
            return $this->_parameters[$name];
        if (isset($this->_get[$name]))
            return $this->_get[$name];
        if (isset($this->_post[$name]))
            return $this->_post[$name];
        return null;
    }

    /**
     * @return array $_GET
     */
    public function query():array
    {
        return $this->_get;
    }

    /**
     * Execute the Route's callback and return the Response object that the callback is required to return
     *
     * @return Response
     */
    public function response():Response
    {
        $cb = $this->_route->callback;
        $this->_parameters = $this->_route->parameters;
        return call_user_func_array($cb, [$this]);
    }

    /**
     * Returns the session var at $key
     *
     * First call to this method will initiate the session
     *
     * @TODO Implment dependency injection
     * @param string $key Dot notation for deeper values, i.e. `user.email`
     * @return mixed/null
     */
    public function session(?string $key = null)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        if ($key && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        if ($key && is_string($key) && strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            try {
                return $this->_getArrayValue($keys, $_SESSION);
            } catch (\OutOfBoundsException $e) {
                if ($e->getCode() == 999) {
                    return null;
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * Look for a deep value in a data array.
     *
     * Given $data = ['one' => ['two' => ['three' => 55)), 'four' => [];
     *
     * _getArrayValue(['one','two','three'], $data) will return 55
     * _getArrayValue(['four','five'], $data) will throw exception with code 999
     *
     * @param  mixed $keys
     * @param  mixed $data
     * @return mixed
     * @throws OutOfBoundsException if the key does not exist in data
     */
    private function _getArrayValue($keys, &$data) {
        $key = array_shift($keys);
        if (!is_array($data) || empty($key)) {
            return $data;
        }
        if (empty($keys)) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            } else {
                array_unshift($keys, $key);
                throw new \OutOfBoundsException("Key [" . join('.', $keys) . "] not set in " . print_r($data,true));
            }
        } else {
            if (!array_key_exists($key, $data)) {
                throw new \OutOfBoundsException("Key [" . join('.', $keys) . ".$key] not set in " . print_r($data,true));
            }
            return $this->_getArrayValue($keys, $data[$key]);
        }
    }

    /**
     * Redirect NOW the request to $url
     *
     * @param $url
     */
    public function redirect($url)
    {
        // @TODO add support for reverse route match
        header( "Location: " . $url);
        exit;
    }
}
