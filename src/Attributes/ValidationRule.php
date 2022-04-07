<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;
use romanzipp\LaravelDTO\Attributes\Interfaces\DataAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\ValidationRuleAttributeInterface;

#[Attribute]
class ValidationRule implements DataAttributeInterface, ValidationRuleAttributeInterface
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
