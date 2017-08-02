<?php

namespace alkemann\h2l\tests\mocks\relationship;

use alkemann\h2l\{Entity, Model};

class Father
{
    use Entity, Model;
    static $connection = 'EntityRelationshipTest';
    static $table = 'fathers';
    static $fields = ['id', 'name', 'job'];
    static $relations = [
        'children' => [
            Son::class => 'father_id'
        ],
        'sons' => [
            'class' => Son::class,
            'local' => 'id',
            'foreign' => 'father_id',
            'type' => 'has_many'
        ]
    ];
}
