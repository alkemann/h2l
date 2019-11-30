<?php

namespace alkemann\h2l\traits;

/**
 * Class Entity
 *
 * @property array $relations
 * @package alkemann\h2l
 */
trait Entity
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Cache of loaded relationships
     *
     * @var array
     */
    protected array $relationships = [];

    /**
     * Entity constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    abstract public static function fields(): ?array;

    /**
     * @param string $name
     * @return null|mixed
     */
    public function __get(string $name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * @param string $method
     * @param array $args
     * @return null|array|object
     */
    public function __call(string $method, array $args = [])
    {
        $refresh = (bool) array_shift($args);
        return $this->getRelatedModel($method, $refresh);
    }

    /**
     * @param string $name
     * @param bool $refresh
     * @return null|array|object
     */
    private function getRelatedModel(string $name, bool $refresh = false)
    {
        $this->checkIfRelationIsSet($name);
        if ($refresh === false && array_key_exists($name, $this->relationships)) {
            return $this->relationships[$name];
        }
        return $this->populateRelation($name);
    }

    /**
     * @param string $name
     */
    private function checkIfRelationIsSet(string $name): void
    {
        if (isset(static::$relations) === false || is_array(static::$relations) === false ||
            array_key_exists($name, static::$relations) === false) {
            $class = get_class($this);
            throw new \Error("Call to undefined method {$class}::{$name}()");
        }
    }

    /**
     * @param string[] ...$relation_names any list of relations to return
     * @return object instance of class that has Entity trait
     */
    public function with(string ...$relation_names): object
    {
        foreach ($relation_names as $name) {
            $this->populateRelation($name);
        }
        return $this;
    }

    /**
     * @param string $relation_name
     * @param array|object|null $data
     * @return array|object|null array in case of has_many
     * @throws \Exception
     */
    public function populateRelation(string $relation_name, $data = null)
    {
        if ($data !== null) {
            $this->relationships[$relation_name] = $data;
            return;
        }
        $relationship = $this->describeRelationship($relation_name);
        $relation_class = $relationship['class'];
        $relation_id = $this->{$relationship['local']};
        if ($relationship['type'] === 'belongs_to') {
            $related = $relation_class::get($relation_id);
        } elseif ($relationship['type'] === 'has_one') {
            $related_by = $relationship['foreign'];
            $result = $relation_class::findAsArray([$related_by => $relation_id], ['limit' => 1]);
            $related = $result ? current($result) : null;
        } elseif ($relationship['type'] === 'has_many') { // type must be has_many
            $related_by = $relationship['foreign'];
            $related = $relation_class::findAsArray([$related_by => $relation_id]);
        } else {
            throw new \Exception("Not a valid relationship type [" . $relationship['type'] . ']');
        }
        $this->relationships[$relation_name] = $related;
        return $related;
    }

    /**
     * @param string $name
     * @return array
     * @throws \Error if $name is for an unspecified relation
     */
    public function describeRelationship(string $name): array
    {
        $this->checkIfRelationIsSet($name);
        $settings = static::$relations[$name];
        if (sizeof($settings) === 1) {
            return $this->generateRelationshipFromShorthand($settings);
        }
        if (!array_key_exists('local', $settings)) {
            $settings['local'] = 'id';
        }
        if (!array_key_exists('foreign', $settings)) {
            $settings['foreign'] = 'id';
        }
        if (!array_key_exists('type', $settings)) {
            $settings['type'] = $settings['local'] === 'id' ? 'has_many' : 'belongs_to';
        }

        return $settings;
    }

    /**
     * @param array $settings
     * @return array
     */
    private function generateRelationshipFromShorthand(array $settings): array
    {
        $field = current($settings);
        $fields = static::fields() ?? [];
        $field_is_local = in_array($field, $fields);
        if ($field_is_local) {
            return [
                'class' => key($settings),
                'type' => 'belongs_to',
                'local' => $field,
                'foreign' => 'id'
            ];
        } else {
            return [
                'class' => key($settings),
                'type' => 'has_many',
                'local' => 'id',
                'foreign' => $field
            ];
        }
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
        $this->relationships = [];
    }

    /**
     * Cast the data array to $type and return this
     *
     * @param string $type json|array
     * @return string|array
     * @throws \InvalidArgumentException on unsupported type
     */
    public function to(string $type)
    {
        switch ($type) {
            case 'array': return $this->data;
            case 'json': return json_encode($this->data);
            default:
                throw new \InvalidArgumentException("Unkown type $type");
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
