<?php

namespace romanzipp\LaravelDTO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use romanzipp\DTO\AbstractData;
use romanzipp\DTO\Exceptions\InvalidDataException;
use romanzipp\LaravelDTO\Attributes\ForModel;
use RuntimeException;

abstract class AbstractModelData extends AbstractData
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $data = [])
    {
        $properties = Property::collectFromClass(static::class);

        $validationRules = [];
        $validationData = [];

        foreach ($properties as $property) {
            if ( ! empty($rules = $property->getValidationRules())) {
                $validationRules[$property->getName()] = $rules;
            }

            if (array_key_exists($property->getName(), $data)) {
                $validationData[$property->getName()] = $data[$property->getName()];
            }
        }

        $validator = Validator::make($validationData, $validationRules);
        $validator->validate();

        // Detected nested items

        foreach ($properties as $property) {
            if ($nestedClass = $property->getNestedClass()) {
                $nestedData = [];
                foreach ($data[$property->getName()] as $datum) {
                    $nestedData[] = new $nestedClass($datum);
                }

                $data[$property->getName()] = $nestedData;
            }
        }

        try {
            parent::__construct($data);
        } catch (InvalidDataException $exception) {
            $messages = [];

            foreach ($exception->getProperties() as $property) {
                $messages[$property->getName()] = "The {$property->getName()} field is invalid.";
            }

            throw ValidationException::withMessages($messages);
        }
    }

    /**
     * Take request input and fill into DTO base on the #[RequestAttribute].
     *
     * @see \romanzipp\LaravelDTO\Attributes\RequestAttribute
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return static
     */
    public static function fromRequest(Request $request): static
    {
        $payload = $request->input();
        $data = [];

        foreach (Property::collectFromClass(static::class) as $property) {
            $requestAttribute = $property->getRequestAttribute();

            if (null === $requestAttribute || ! isset($payload[$requestAttribute])) {
                continue;
            }

            $data[$property->getName()] = $payload[$requestAttribute];
        }

        return new static($data);
    }

    public function toModel(): Model
    {
        $modelClass = null;

        $reflectionClass = new ReflectionClass(static::class);

        foreach ($reflectionClass->getAttributes(ForModel::class) as $attribute) {
            /** @var \romanzipp\LaravelDTO\Attributes\ForModel $attributeInstance */
            $attributeInstance = $attribute->newInstance();
            $modelClass = $attributeInstance->model;
            break;
        }

        if (null === $modelClass) {
            throw new RuntimeException('No model defined for DTO');
        }

        $attributes = [];

        foreach (Property::collectFromClass(static::class) as $property) {
            $modelAttribute = $property->getModelAttribute();

            if (null === $modelAttribute || ! $this->isset($property->getName())) {
                continue;
            }

            $attributes[$modelAttribute] = $this->{$property->getName()};
        }

        /** @var \Illuminate\Database\Eloquent\Model $modelClass */
        return new $modelClass($attributes);
    }
}
