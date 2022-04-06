<?php

namespace romanzipp\LaravelDTO;

use ReflectionClass;
use ReflectionProperty;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

class Property
{
    private string $name;

    private array $validationRules = [];

    private ?string $modelAttribute = null;

    private ?string $requestAttribute = null;

    public function __construct(ReflectionProperty $reflectionProperty)
    {
        $this->name = $reflectionProperty->getName();

        foreach ($reflectionProperty->getAttributes() as $reflectionAttribute) {
            switch ($reflectionAttribute->getName()) {
                case ValidationRule::class:
                    /** @var \romanzipp\LaravelDTO\Attributes\ValidationRule $attributeInstance */
                    $attributeInstance = $reflectionAttribute->newInstance();
                    $this->validationRules = $attributeInstance->getRules();
                    break;

                case ModelAttribute::class:
                    /** @var \romanzipp\LaravelDTO\Attributes\ModelAttribute $attributeInstance */
                    $attributeInstance = $reflectionAttribute->newInstance();
                    $this->modelAttribute = $attributeInstance->getName() ?? $this->name;
                    break;

                case RequestAttribute::class:
                    /** @var \romanzipp\LaravelDTO\Attributes\RequestAttribute $attributeInstance */
                    $attributeInstance = $reflectionAttribute->newInstance();
                    $this->requestAttribute = $attributeInstance->getName() ?? $this->name;
                    break;
            }
        }
    }

    /**
     * @return self[]
     */
    public static function collectFromClass(string $class): array
    {
        $properties = [];

        $reflectionClass = new ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $properties[] = new self($reflectionProperty);
        }

        return $properties;
    }

    public function getName(): string
    {
        return $this->name;
    }

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
}
