<?php

namespace romanzipp\LaravelDTO;

use romanzipp\LaravelDTO\Attributes\Casts\CastInterface;
use romanzipp\LaravelDTO\Attributes\Conversions\ConversionInterface;
use romanzipp\LaravelDTO\Attributes\Conversions\ConvertsFromEnum;
use romanzipp\LaravelDTO\Attributes\Interfaces;

class Property
{
    private string $name;

    /**
     * @var mixed[]
     */
    private array $validationRules = [];

    private ?string $modelAttribute = null;

    private ?string $requestAttribute = null;

    private ?string $nestedClass = null;

    private bool $hasDefaultValue;

    private mixed $defaultValue;

    private ?CastInterface $cast = null;

    private ?ConversionInterface $conversion = null;

    private ?string $nestedParentKey = null;

    public function __construct(\ReflectionProperty $reflectionProperty)
    {
        $this->name = $reflectionProperty->getName();
        $this->hasDefaultValue = $reflectionProperty->hasDefaultValue();

        if ($this->hasDefaultValue) {
            $this->defaultValue = $reflectionProperty->getDefaultValue();
        }

        foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
            $attributeInstance = $reflectionAttribute->newInstance();

            if ($attributeInstance instanceof Interfaces\ValidationRuleAttributeInterface) {
                $this->validationRules = $attributeInstance->getRules();
            }

            if ($attributeInstance instanceof Interfaces\ModelAttributeInterface) {
                $this->modelAttribute = $attributeInstance->getModelAttribute() ?? $this->name;
            }

            if ($attributeInstance instanceof Interfaces\RequestAttributeInterface) {
                $this->requestAttribute = $attributeInstance->getRequestAttribute() ?? $this->name;
            }

            if ($attributeInstance instanceof Attributes\NestedModelData) {
                $this->nestedClass = $attributeInstance->getModelDataClass();
            }

            if ($attributeInstance instanceof CastInterface) {
                $this->cast = $attributeInstance;
            }

            if ($attributeInstance instanceof ConversionInterface) {
                $this->conversion = $attributeInstance;
            }
        }
    }

    /**
     * @return self[]
     */
    public static function collectFromClass(string $class): array
    {
        $properties = [];

        $reflectionClass = new \ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $properties[] = new self($reflectionProperty);
        }

        return $properties;
    }

    public function setNestedParentKey(string $key): void
    {
        $this->nestedParentKey = $key;
    }

    public function getValidatorName(): string
    {
        if (isset($this->nestedParentKey)) {
            return "{$this->nestedParentKey}.{$this->getName()}";
        }

        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed[]
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getModelAttribute(): ?string
    {
        return $this->modelAttribute;
    }

    public function getRequestAttribute(): ?string
    {
        return $this->requestAttribute;
    }

    public function getNestedClass(): ?string
    {
        return $this->nestedClass;
    }

    public function getValidatorKeyName(): string
    {
        return $this->requestAttribute ?? $this->name;
    }

    public function hasCast(): bool
    {
        return null !== $this->cast;
    }

    public function getCastedType(mixed $value): mixed
    {
        return $this->cast->castToType($value);
    }

    public function getConvertedValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $this->conversion = $this->conversion ?? new ConvertsFromEnum();
        }

        if (null === $this->conversion) {
            return $value;
        }

        return $this->conversion->convert($value);
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
}
