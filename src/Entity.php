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

    protected $relationships = [];

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

    public function __call(string $method, array $args = [])
    {
        array_unshift($args, $method);
        return call_user_func_array([self::class, 'getRelatedModel'], $args);
    }

    private function getRelatedModel(string $name, bool $refresh = false)
    {
        if (array_key_exists($name, static::$relations) === false) {
            $class = get_class($this);
            throw new \Exception("The [$name] relation is not specified for [$class]");
        }
        if ($refresh === false && array_key_exists($name, $this->relationships)) {
            return $this->relationships[$name];
        }
        return $this->populateRelation($name);
    }

    /**
     * @return object|array array in case of has_many
     */
    private function populateRelation(string $relation_name)
    {
        $relationship = $this->describeRelationship($relation_name);
        $relation_class = $relationship['class'];
        $relation_id = $this->{$relationship['local']};
        if ($relationship['type'] === 'belongs_to') {
            $related = $relation_class::get($relation_id);
        } elseif ($relationship['type'] === 'has_one') {
            $related_by = $relationship['foreign'];
            $result = $relation_class::findAsArray([$related_by => $relation_id], ['limit' => 1]);
            $related = $result ? $result[0] : null;
        } elseif ($relationship['type'] === 'has_many') { // type must be has_many
            $related_by = $relationship['foreign'];
            $related = $relation_class::findAsArray([$related_by => $relation_id]);
        } else {
            throw new \Exception("Not a valid relationship type [" . $relationship['type'] . ']');
        }
        $relationships[$relation_name] = $related;
        return $related;
    }

    public function describeRelationship(string $name): array
    {
        $settings = static::$relations[$name];
        if (sizeof($settings) === 1) {
            $field = current($settings);
            $field_is_local = in_array($field, static::$fields); // @TODO hack to use Model data?
            if ($field_is_local) {
                $settings = [
                    'class' => key($settings),
                    'type' => 'belongs_to',
                    'local' => $field,
                    'foreign' => 'id'
                ];
            } else {
                $settings = [
                    'class' => key($settings),
                    'type' => 'has_many',
                    'local' => 'id',
                    'foreign' => $field
                ];
            }

        }
        if (!array_key_exists('local', $settings)) {
            $settings['local'] = 'id';
        }
        if (!array_key_exists('type', $settings)) {
            $settings['type'] = $settings['local'] === 'id' ? 'has_many' : 'belongs_to';
        }

        return $settings;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function data(array $data = null): array
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
    public function reset(): void
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
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
