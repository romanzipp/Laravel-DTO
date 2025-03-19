<?php

namespace romanzipp\LaravelDTO\Attributes;

use romanzipp\LaravelDTO\Attributes\Interfaces\DataAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\ValidationRuleChildrenAttributeInterface;

#[\Attribute]
class ValidationChildrenRule implements DataAttributeInterface, ValidationRuleChildrenAttributeInterface
{
    /**
     * @param mixed[] $rules
     */
    public function __construct(
        private array $rules,
        private string $accessor = '*',
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getAccessor(): string
    {
        return $this->accessor;
    }
}
