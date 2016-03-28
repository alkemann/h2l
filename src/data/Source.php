<?php

namespace alkemann\h2l\data;

interface Source
{
    public function __construct(array $config = []);
    public function query($query, array $params = []);
    public function find($table, array $conditions, array $options = []);
    public function update($table, array $conditions, array $data, array $options = []);
    public function insert($table, array $data, array $options = []);
    public function delete($table, array $conditions, array $options = []);
}
