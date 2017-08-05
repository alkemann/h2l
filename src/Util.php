<?php

namespace alkemann\h2l;

use OutOfBoundsException;

/**
 * Class Util
 *
 * @package alkemann\h2l
 */
class Util
{

    /**
     * Look for a deeo value in a nested data array.
     *
     * Given $data = ['one' => ['two' => ['three' => 55]], 'four' => []];
     *
     * ```php
     *  getFromArrayByKey('one.two.three', $data) -> 55
     *  getFromArrayByKey('one|two', $data, '|') -> ['three' => 55]
     *  getFromArrayByKey('four.five', $data) -> throws OutOfBoundsException
     * ```
     *
     * @param string $key
     * @param array $data
     * @param string $delimiter
     * @return mixed|null
     */
    public static function getFromArrayByKey(string $key, array &$data, string $delimiter = '.')
    {
        $keys = explode($delimiter, $key);
        try {
            return self::getArrayValueByKeys($keys, $data);
        } catch (\OutOfBoundsException $e) {
            return null;
        }
    }

    /**
     * Look for a deep value in a data array.
     *
     * Given $data = ['one' => ['two' => ['three' => 55]], 'four' => []];
     *
     * ```php
     *  getArrayValueByKeys(['one','two','three'], $data) will return 55
     *  getArrayValueByKeys(['four','five'], $data) will throw OutOfBoundsException
     * ```
     *
     * @param  mixed $keys
     * @param  mixed $data
     * @return mixed
     * @throws OutOfBoundsException if the key does not exist in data
     * @codeCoverageIgnore
     */
    public static function getArrayValueByKeys(array $keys, &$data)
    {
        $key = array_shift($keys);
        if (!is_array($data) || empty($key)) {
            return $data;
        }
        if (empty($keys)) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            } else {
                array_unshift($keys, $key);
                throw new OutOfBoundsException("Key [" . join('.', $keys) . "] not set in " . print_r($data, 1));
            }
        } else {
            if (!array_key_exists($key, $data)) {
                throw new OutOfBoundsException("Key [" . join('.', $keys) . ".$key] not set in " . print_r($data, 1));
            }
            return self::getArrayValueByKeys($keys, $data[$key]);
        }
    }

    public static function getRequestHeaders(array $server_array)
    {
        $out = [];
        foreach ($server_array as $name => $value) {
            if (substr($name, 0, 5) == "HTTP_") {
                $name = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))));
                $out[$name] = $value;
            } elseif ($name == "CONTENT_TYPE") {
                $out["Content-Type"] = $value;
            } else if ($name == "CONTENT_LENGTH") {
                $out["Content-Length"] = $value;
            }
        }

        return $out;
    }
}
