<?php

namespace alkemann\h2l\interfaces;

interface Source
{
    public function __construct(array $config = []);

    public function query($query, array $params = []);

    public function one(string $table, array $conditions, array $options = []):?array;

    public function find(string $table, array $conditions, array $options = []);

    public function update(string $table, array $conditions, array $data, array $options = []);

    public function insert(string $table, array $data, array $options = []);

    public function delete(string $table, array $conditions, array $options = []);
}
