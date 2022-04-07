<?php

namespace romanzipp\LaravelDTO\Attributes\Casts;

interface CastInterface
{
    public function castToType(mixed $value): mixed;
}
