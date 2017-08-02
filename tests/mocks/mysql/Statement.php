<?php

namespace alkemann\h2l\tests\mocks\mysql;

class Statement implements \Iterator
{
    public $name = "Iterator";
    protected $ec = null;
    protected $result = [];
    public function __construct(\closure $execute_check, array $result = []) {
        $this->ec = $execute_check;
        $this->result = $result;
    }
    public function current() { return current($this->result); }
    public function key() { return key($this->result); }
    public function next() { next($this->result); }
    public function rewind() { reset($this->result); }
    public function valid() { return current($this->result) !== false; }
    public function execute($v = []) { $ec = $this->ec; return $ec($v); }
    public function fetch() { return $this->current(); }
    public function bindValue($key, $value) {}
    public function rowCount() { return sizeof($this->result); }
}
