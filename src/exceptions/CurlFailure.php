<?php declare(strict_types=1);

namespace alkemann\h2l\exceptions;

/**
 * Class CurlFailure
 *
 * @package alkemann\h2l\exceptions
 */
class CurlFailure extends \Exception
{
    /** @var array */
    protected $context = [];

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * @TODO Use constants?
     * @var array<int, string>
     */
    public static $code_to_constant_name = [
        1 => "CURLE_UNSUPPORTED_PROTOCOL",
        2 => "CURLE_FAILED_INIT",
        3 => "CURLE_URL_MALFORMAT",
        4 => "CURLE_URL_MALFORMAT_USER",
        5 => "CURLE_COULDNT_RESOLVE_PROXY",
        6 => "CURLE_COULDNT_RESOLVE_HOST",
        7 => "CURLE_COULDNT_CONNECT",
        8 => "CURLE_FTP_WEIRD_SERVER_REPLY",
        9 => "CURLE_FTP_ACCESS_DENIED",
        10 => "CURLE_FTP_USER_PASSWORD_INCORRECT",
        11 => "CURLE_FTP_WEIRD_PASS_REPLY",
        12 => "CURLE_FTP_WEIRD_USER_REPLY",
        13 => "CURLE_FTP_WEIRD_PASV_REPLY",
        14 => "CURLE_FTP_WEIRD_227_FORMAT",
        15 => "CURLE_FTP_CANT_GET_HOST",
        16 => "CURLE_FTP_CANT_RECONNECT",
        17 => "CURLE_FTP_COULDNT_SET_BINARY",
        // 18 => "CURLE_FTP_PARTIAL_FILE",
        18 => "CURLE_PARTIAL_FILE",
        19 => "CURLE_FTP_COULDNT_RETR_FILE",
        20 => "CURLE_FTP_WRITE_ERROR",
        21 => "CURLE_FTP_QUOTE_ERROR",
        22 => "CURLE_HTTP_NOT_FOUND",
        // 22 => "CURLE_HTTP_RETURNED_ERROR",
        23 => "CURLE_WRITE_ERROR",
        24 => "CURLE_MALFORMAT_USER",
        25 => "CURLE_FTP_COULDNT_STOR_FILE",
        26 => "CURLE_READ_ERROR",
        27 => "CURLE_OUT_OF_MEMORY",
        28 => "CURLE_OPERATION_TIMEDOUT",
        // 28 => "CURLE_OPERATION_TIMEOUTED",
        29 => "CURLE_FTP_COULDNT_SET_ASCII",
        30 => "CURLE_FTP_PORT_FAILED",
        31 => "CURLE_FTP_COULDNT_USE_REST",
        32 => "CURLE_FTP_COULDNT_GET_SIZE",
        33 => "CURLE_HTTP_RANGE_ERROR",
        34 => "CURLE_HTTP_POST_ERROR",
        35 => "CURLE_SSL_CONNECT_ERROR",
        36 => "CURLE_BAD_DOWNLOAD_RESUME",
        // 36 => "CURLE_FTP_BAD_DOWNLOAD_RESUME",
        37 => "CURLE_FILE_COULDNT_READ_FILE",
        38 => "CURLE_LDAP_CANNOT_BIND",
        39 => "CURLE_LDAP_SEARCH_FAILED",
        40 => "CURLE_LIBRARY_NOT_FOUND",
        41 => "CURLE_FUNCTION_NOT_FOUND",
        42 => "CURLE_ABORTED_BY_CALLBACK",
        43 => "CURLE_BAD_FUNCTION_ARGUMENT",
        44 => "CURLE_BAD_CALLING_ORDER",
        45 => "CURLE_HTTP_PORT_FAILED",
        46 => "CURLE_BAD_PASSWORD_ENTERED",
        47 => "CURLE_TOO_MANY_REDIRECTS",
        48 => "CURLE_UNKNOWN_TELNET_OPTION",
        49 => "CURLE_TELNET_OPTION_SYNTAX",
        50 => "CURLE_OBSOLETE",
        51 => "CURLE_SSL_PEER_CERTIFICATE",
        52 => "CURLE_GOT_NOTHING",
        53 => "CURLE_SSL_ENGINE_NOTFOUND",
        54 => "CURLE_SSL_ENGINE_SETFAILED",
        55 => "CURLE_SEND_ERROR",
        56 => "CURLE_RECV_ERROR",
        57 => "CURLE_SHARE_IN_USE",
        58 => "CURLE_SSL_CERTPROBLEM",
        59 => "CURLE_SSL_CIPHER",
        60 => "CURLE_SSL_CACERT",
        61 => "CURLE_BAD_CONTENT_ENCODING",
        62 => "CURLE_LDAP_INVALID_URL",
        63 => "CURLE_FILESIZE_EXCEEDED",
        64 => "CURLE_FTP_SSL_FAILED",
        79 => "CURLE_SSH"
    ];
}
