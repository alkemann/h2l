<?php

namespace alkemann\h2l\tests\mocks\relationship;

use alkemann\h2l\traits\{ Model, Entity };

/**
 * Class Father
 *
 * @method array sons() returns an array of Sons
 */
class Father
{
    use Entity, Model;
    static $connection = 'EntityRelationshipTest';
    static $table = 'fathers';
    static $fields = ['id', 'name', 'job'];
    static $pk = 'id';
    static $relations = [
        'children' => [
            Son::class => 'father_id'
        ],
        'sons' => [
            'class' => Son::class,
            'foreign' => 'father_id',
            'local' => 'id',
            'type' => 'has_many'
        ],
    ];
}
