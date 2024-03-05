<?php

namespace romanzipp\LaravelDTO\Attributes\Interfaces;

interface ValidationRuleChildrenAttributeInterface
{
    /**
     * @return mixed[]
     */
    public function getRules(): array;

    public function getAccessor(): string;
}
