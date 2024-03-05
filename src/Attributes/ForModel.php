<?php

namespace romanzipp\LaravelDTO\Attributes;

#[\Attribute]
class ForModel
{
    public function __construct(
        public string $model
    ) {
    }
}
