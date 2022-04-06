<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;

#[Attribute]
class ValidatesInput implements DTOAttribute
{
    public array $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}
