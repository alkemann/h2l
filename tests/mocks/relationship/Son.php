<?php

namespace alkemann\h2l\tests\mocks\relationship;

use alkemann\h2l\traits\{ Model, Entity };

class Son
{
    use Entity, Model;
    static $connection = 'EntityRelationshipTest';
    static $table = 'sons';
    static $fields = ['id', 'father_id', 'name', 'age'];
    static $relations = [
        'father' => [
            'class' => Father::class,
            'local' => 'father_id',
            'foreign' => 'id',
            'type' => 'belongs_to'
        ],
        'dad' => [
            Father::class => 'father_id'
        ],
        'car' => [
            'class' => Car::class,
            'local' => 'id',
            'foreign' => 'owner_id',
            'type' => 'has_one'
        ]
    ];
}
