<?php

namespace romanzipp\LaravelDTO\Attributes;

use Attribute;
use romanzipp\LaravelDTO\Attributes\Interfaces\DataAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\ModelAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\RequestAttributeInterface;
use romanzipp\LaravelDTO\Attributes\Interfaces\ValidationRuleAttributeInterface;

#[Attribute]
class ValidatedRequestModelAttribute implements DataAttributeInterface, ValidationRuleAttributeInterface, RequestAttributeInterface, ModelAttributeInterface
{
    /**
     * @param mixed[] $rules
     * @param string|null $requestAttribute
     * @param string|null $modelAttribute
     */
    public function __construct(
        private array $rules = [],
        private ?string $requestAttribute = null,
        private ?string $modelAttribute = null
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getRequestAttribute(): ?string
    {
        return $this->requestAttribute;
    }

    public function getModelAttribute(): ?string
    {
        return $this->modelAttribute;
    }
}
