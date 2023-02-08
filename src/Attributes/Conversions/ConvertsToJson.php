<?php

namespace romanzipp\LaravelDTO\Attributes\Conversions;

#[\Attribute]
class ConvertsToJson implements ConversionInterface
{
    public function convert(mixed $value): string|bool
    {
        return json_encode($value);
    }
}
