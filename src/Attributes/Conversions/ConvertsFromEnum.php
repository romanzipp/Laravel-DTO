<?php

namespace romanzipp\LaravelDTO\Attributes\Conversions;

#[\Attribute]
class ConvertsFromEnum implements ConversionInterface
{
    public function convert(mixed $value): string|int
    {
        if ( ! ($value instanceof \BackedEnum)) {
            throw new \RuntimeException('Unexpected type in enum conversion');
        }

        return $value->value;
    }
}
