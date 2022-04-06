<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ModelAttribute implements DTOAttribute
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
