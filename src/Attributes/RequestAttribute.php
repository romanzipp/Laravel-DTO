<?php

namespace romanzipp\LaravelDTO\Attributes;

use romanzipp\LaravelDTO\Attributes\Interfaces\DataAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\RequestAttributeInterface;

#[\Attribute]
class RequestAttribute implements DataAttributeInterface, RequestAttributeInterface
{
    public function __construct(
        private ?string $name = null,
    ) {
    }

    public function getRequestAttribute(): ?string
    {
        return $this->name;
    }
}
