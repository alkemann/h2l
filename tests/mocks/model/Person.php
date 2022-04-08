<?php

namespace alkemann\h2l\tests\mocks\model;

use alkemann\h2l\traits\Model;

class Person {
    use Model;
    public static $pk = 'pid';
    public static $table = 'people';
    public static $fields = ['pid', 'name'];
    public static $connection = 'default';
    public $data = [];
    public $pid = null;

    // Methods normally provided by \alkemann\h2l\Entity
    public function __construct(array $data = []) { $this->data($data); }
    public function reset(): void { $this->data = []; }
    public function with(string ...$relation_names): object {}
    public function data(?array $data = null): array
    {
        if ($data === null) return $this->data;
        $this->data = $data;
        $pk = static::$pk;
        if (isset($data[$pk])) {
            $this->{$pk} = $data[$pk];
        }
        return $this->data;
    }
}
