<?php

namespace alkemann\h2l\util;

/**
 * Class Http
 *
 * Holds HTTP Spec constants,
 *
 * @package alkemann\h2l\util
 */
class Http
{
    public const TRACE = 'TRACE';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const CONNECT = 'CONNECT';
    public const OPTIONS = 'OPTIONS';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';
    public const GET = 'GET';

    public const CONTENT_HTML = 'text/html';
    public const CONTENT_TEXT_XML = 'text/xml';
    public const CONTENT_XML = 'application/xml';
    public const CONTENT_TEXT = 'text/plain';
    public const CONTENT_FORM = 'application/x-www-form-urlencoded';
    public const CONTENT_JSON = 'application/json';

    public const CODE_CONTINUE = 100;
    public const CODE_SWITCHING_PROTOCOLS = 101;

    public const CODE_OK = 200;
    public const CODE_CREATED = 201;
    public const CODE_ACCEPTED = 202;
    public const CODE_NON_AUTHORITATIVE_INFORMATION = 203;
    public const CODE_NO_CONTENT = 204;
    public const CODE_RESET_CONTENT = 205;
    public const CODE_PARTIAL_CONTENT = 206;

    public const CODE_MULTIPLE_CHOICES = 300;
    public const CODE_MOVED_PERMANENTLY = 301;
    public const CODE_FOUND = 302;
    public const CODE_SEE_OTHER = 303;
    public const CODE_NOT_MODIFIED = 304;
    public const CODE_USE_PROXY = 305;
    public const CODE_TEMPORARY_REDIRECT = 307;

    public const CODE_BAD_REQUEST = 400;
    public const CODE_UNAUTHORIZED = 401;
    public const CODE_PAYMENT_REQUIRED = 402;
    public const CODE_FORBIDDEN = 403;
    public const CODE_NOT_FOUND = 404;
    public const CODE_METHOD_NOT_ALLOWED = 405;
    public const CODE_NOT_ACCEPTABLE = 406;
    public const CODE_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const CODE_REQUEST_TIMEOUT = 408;
    public const CODE_CONFLICT = 409;
    public const CODE_GONE = 410;
    public const CODE_LENGTH_REQUIRED = 411;
    public const CODE_PRECONDITION_FAILED = 412;
    public const CODE_REQUEST_ENTITY_TOO_LARGE = 413;
    public const CODE_REQUEST_URI_TOO_LONG = 414;
    public const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    public const CODE_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const CODE_EXPECTATION_FAILED = 417;

    public const CODE_INTERNAL_SERVER_ERROR = 500;
    public const CODE_NOT_IMPLEMENTED = 501;
    public const CODE_BAD_GATEWAY = 502;
    public const CODE_SERVICE_UNAVAILABLE = 503;
    public const CODE_GATEWAY_TIMEOUT = 504;
    public const CODE_HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var array<string, string>
     */
    protected static $contentTypeToFileEnding = [
        'text/html' => 'html',
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/plain' => 'txt',
    ];

    /**
     * @var array<int, string>
     */
    private static $code_to_message = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    /**
     * Convert a content type, i.e. "appliction/json", to a file ending, i.e. json"
     *
     * @param string $type
     * @return string
     */
    public static function fileEndingFromType(string $type): string
    {
        foreach (self::$contentTypeToFileEnding as $type_key => $ending) {
            if ($type === $type_key) {
                return $ending;
            }
        }
        return 'html';
    }

    /**
     * Convert a file ending, i.e. ".json", into a content type, i.e. "appliction/json"
     *
     * @param string $ending
     * @return string
     */
    public static function contentTypeFromFileEnding(string $ending): string
    {
        $type = array_search($ending, static::$contentTypeToFileEnding);
        return $type == false ? 'text/html' : (string) $type;
    }

    /**
     * Convert and return the string "name" of a HTTP response code, or "Unknown"
     *
     * @param int $code
     * @return string
     */
    public static function httpCodeToMessage(int $code): string
    {
        return self::$code_to_message[$code] ?? "Unknown";
    }

    /**
     * Returns the request headers of a PHP Server array
     *
     * @param array $server_array
     * @return array
     */
    public static function getRequestHeadersFromServerArray(array $server_array): array
    {
        $out = [];
        foreach ($server_array as $name => $value) {
            if (substr($name, 0, 5) == "HTTP_") {
                $name = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))));
                $out[$name] = $value;
            }
        }
        if (array_key_exists("CONTENT_TYPE", $server_array)) {
            $out["Content-Type"] = $server_array['CONTENT_TYPE'];
        }
        if (array_key_exists("CONTENT_LENGTH", $server_array)) {
            $out["Content-Length"] = $server_array['CONTENT_LENGTH'];
        }
        return $out;
    }
}
