<?php

namespace romanzipp\LaravelDTO\Attributes\Casts;

use Attribute;

#[\Attribute]
class CastToDate implements CastInterface
{
    public function __construct(
        private ?string $dateClass = null,
    ) {
    }

    public function castToType(mixed $value): mixed
    {
        /**
         * @var \Carbon\CarbonInterface $dateClass
         */
        $dateClass = $this->getDateClass();

        return $dateClass::make($value);
    }

    public function getDateClass(): string
    {
        return $this->dateClass ?? get_class(now());
    }
}
