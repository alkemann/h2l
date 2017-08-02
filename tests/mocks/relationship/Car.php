<?php

namespace alkemann\h2l\tests\mocks\relationship;

use alkemann\h2l\{Entity, Model};

class Car
{
    use Entity, Model;
    static $connection = 'EntityRelationshipTest';
    static $table = 'cars';
    static $fields = ['id', 'owner_id', 'brand'];
}
