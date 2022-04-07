<?php

namespace romanzipp\LaravelDTO\Attributes\Interfaces;

interface ValidationRuleAttributeInterface
{
    /**
     * @return mixed[]
     */
    public function getRules(): array;
}
