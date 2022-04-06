<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class RequestAttribute
{
    public function __construct(
        private ?string $name = null
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
