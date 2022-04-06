<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ForModel
{
    public function __construct(
        public string $model
    ) {
    }
}
