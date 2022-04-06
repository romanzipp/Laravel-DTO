<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ValidationRule implements DTOAttribute
{
    /**
     * @param mixed[] $rules
     */
    public function __construct(
        private array $rules
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
