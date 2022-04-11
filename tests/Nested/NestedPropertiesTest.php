<?php

namespace romanzipp\LaravelDTO\Tests\Nested;

use romanzipp\LaravelDTO\Tests\TestCase;

class NestedPropertiesTest extends TestCase
{
    public function testNestedPropertyMissing()
    {
        $data = new NestedParentData();

        self::assertInstanceOf(NestedParentData::class, $data);
    }
}
