<?php

namespace backend\entities;

use alkemann\h2l\traits\Entity;

class Examplar implements \JsonSerializable
{
    use Entity;

    public function fields(): array
    {
        return ['id', 'word', "meaning"];
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public static function find(array $query = []): array
    {
        return [
            new Examplar(['id' => 1, 'word' => 'one']),
            new Examplar(['id' => 2, 'word' => 'two']),
            new Examplar(['id' => 3, 'word' => 'three']),
        ];
    }
}