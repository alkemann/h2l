<?php

namespace alkemann\h2l\data;

interface Source
{
    public function __construct(array $config = []):void;
    public function query($query, array $params = []);
    public function find(string $table, array $conditions, array $options = []);
    public function update(string $table, array $conditions, array $data, array $options = []):int;
    public function insert(string $table, array $data, array $options = []);
    public function delete(string $table, array $conditions, array $options = []);
}
