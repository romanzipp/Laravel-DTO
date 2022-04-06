<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ValidationRule implements DTOAttribute
{
    /**
     * @param array $rules
     */
    public function __construct(
        private array $rules
    ) {
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
