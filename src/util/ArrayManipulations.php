<?php declare(strict_types=1);

namespace alkemann\h2l\util;

use OutOfBoundsException;
use InvalidArgumentException;

/**
 * Class Util
 *
 * @package alkemann\h2l
 */
class ArrayManipulations
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
     * @param array<string, mixed> $data
     * @param string $delimiter
     * @return mixed|null
     * @throws InvalidArgumentException if $delimiter is empty string
     */
    public static function getFromArrayByKey(string $key, array $data, string $delimiter = '.')
    {
        if ($delimiter === '') {
            throw new InvalidArgumentException("Delimiter can't be empty string");
        }
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
     * @param  array<string> $keys
     * @param  mixed $data passed by reference
     * @return mixed
     * @throws OutOfBoundsException if the key does not exist in data
     */
    public static function getArrayValueByKeys(array $keys, &$data)
    {
        $key = array_shift($keys);
        if (!is_array($data) || empty($key)) {
            return $data;
        }
        if (array_key_exists($key, $data) === false) {
            throw new OutOfBoundsException("Key [{$key}." . join('.', $keys) . "] not set in " . print_r($data, true));
        }
        if (empty($keys)) {
            return $data[$key];
        } else {
            return self::getArrayValueByKeys($keys, $data[$key]);
        }
    }

    /**
     * Update or Insert a value in a nested array by "dot" notation string
     *
     * Given $data = ['one' => ['two' => ['three' => 55]], 'four' => []];
     *
     * ```php
     *  setToArrayByKey('one.two.three', 42, $data) // will replace 55 with 42
     *  setToArrayByKey('one.two.five', 42, $data) // will add a second key in the 'two' array
     * ```
     *
     * @param string $key
     * @param mixed $value the value to assign
     * @param array<string, mixed> $data The array to update
     * @param string $delimiter defaults to `.`
     * @throws InvalidArgumentException if $delimiter is empty string
     */
    public static function setToArrayByKey(string $key, $value, array &$data, string $delimiter = '.'): void
    {
        if ($delimiter === '') {
            throw new InvalidArgumentException("Delimiter can't be empty string");
        }
        $keys = explode($delimiter, $key);
        self::setArrayValueByKeys($keys, $value, $data);
    }

    /**
     * Update or Insert value in a nested array
     *
     * @param array<string> $keys
     * @param mixed $value
     * @param mixed $data passed by reference
     */
    public static function setArrayValueByKeys(array $keys, $value, &$data): void
    {
        $key = array_shift($keys);
        if (is_null($key)) {
            throw new InvalidArgumentException("At least one key is required");
        }
        if (empty($keys)) {
            $data[$key] = $value;
        } else {
            if (array_key_exists($key, $data) === false) {
                $data[$key] = [];
            }
            self::setArrayValueByKeys($keys, $value, $data[$key]);
        }
    }
}
