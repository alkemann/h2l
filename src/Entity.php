<?php

namespace alkemann\h2l;

/**
 * Class Entity
 *
 * @package alkemann\h2l
 */
trait Entity
{

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Entity constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value) : void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function data(array $data = null) : array
    {
        if (is_null($data)) {
            return $this->data;
        }
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this->data;
    }

    /**
     * Reset object by removing all data
     */
    public function reset() : void
    {
        $this->data = [];
    }

    /**
     * Cast the data array to $type and return this
     *
     * @param $type 'json', 'array'
     * @return mixed
     * @throws \InvalidArgumentException on unsupported type
     */
    public function to(string $type)
    {
        switch ($type) {
            case 'array':
                return $this->data;
            case 'json':
                return json_encode($this->data);
            default:
                throw new \InvalidArgumentException("Unkown type $type");
                break;
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->data;
    }
}
