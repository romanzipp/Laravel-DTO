<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ValidationRule implements DTOAttribute
{
    public array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}
