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
    public function current(): mixed { return current($this->result); }
    public function key(): mixed { return key($this->result); }
    public function next(): void { next($this->result); }
    public function rewind(): void { reset($this->result); }
    public function valid(): bool { return current($this->result) !== false; }
    public function execute($v = []): mixed { $ec = $this->ec; return $ec($v); }
    public function fetch(): mixed { return $this->current(); }
    public function bindValue($key, $value): void {}
    public function rowCount(): int { return sizeof($this->result); }
}
