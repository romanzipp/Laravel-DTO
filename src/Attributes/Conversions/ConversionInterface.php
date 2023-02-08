<?php

namespace romanzipp\LaravelDTO\Attributes\Conversions;

interface ConversionInterface
{
    public function convert(mixed $value): mixed;
}
