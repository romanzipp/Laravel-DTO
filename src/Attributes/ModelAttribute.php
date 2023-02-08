<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;
use romanzipp\LaravelDTO\Attributes\Interfaces\DataAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\ModelAttributeInterface;

#[\Attribute]
class ModelAttribute implements DataAttributeInterface, ModelAttributeInterface
{
    public function __construct(
        private ?string $attribute = null
    ) {
    }

    public function getModelAttribute(): ?string
    {
        return $this->attribute;
    }
}
