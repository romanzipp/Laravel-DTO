<?php

namespace romanzipp\LaravelDTO\Tests\Nested;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\NestedModelData;

class NestedParentData extends AbstractModelData
{
    #[NestedModelData(NestedParentData::class)]
    public array $children;
}
