<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ModelAttribute
{
    public function __construct(
        private ?string $attribute = null
    ) {
    }

    public function getName(): ?string
    {
        return $this->attribute;
    }
}
