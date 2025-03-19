<?php

namespace romanzipp\LaravelDTO\Attributes;

#[\Attribute]
class NestedModelData
{
    public function __construct(
        private string $modelDataClass,
    ) {
    }

    public function getModelDataClass(): string
    {
        return $this->modelDataClass;
    }
}
