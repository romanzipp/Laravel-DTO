<?php

namespace romanzipp\LaravelDTO;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use romanzipp\DTO\AbstractData;
use romanzipp\DTO\Exceptions\InvalidDataException;
use romanzipp\LaravelDTO\Attributes\ForModel;

abstract class AbstractModelData extends AbstractData
{
    /**
     * @var string[]
     */
    private array $__originalData;

    private const FLAG_IS_REQUEST_DATA = '__is_request_data';

    private const FLAG_SKIP_VALIDATION = '__skip_validation';

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $data = [])
    {
        $properties = Property::collectFromClass(static::class);

        $this->__originalData = $data;

        $validationRules = [];
        $validationData = [];

        foreach ($properties as $property) {
            if ( ! empty($rules = $property->getValidationRules())) {
                $validationRules[$property->getValidatorKeyName()] = $rules;
            }

            if (array_key_exists($property->getName(), $data)) {
                $validationData[$property->getValidatorKeyName()] = $data[$property->getName()];

                if ($property->hasCast()) {
                    $data[$property->getName()] = $property->getCastedType($data[$property->getName()]);
                }
            }
        }

        $validator = Validator::make($validationData, $validationRules);

        if ( ! isset($data[self::FLAG_SKIP_VALIDATION])) {
            $validator->validate();
        }

        // Detected nested items

        foreach ($properties as $property) {
            if ($nestedClass = $property->getNestedClass()) {
                $nestedData = [];

                if ( ! isset($data[$property->getName()])) {
                    continue;
                }

                foreach ($data[$property->getName()] as $datum) {
                    /**
                     * @var $nestedClass \romanzipp\LaravelDTO\AbstractModelData
                     */
                    if (isset($data[self::FLAG_IS_REQUEST_DATA])) {
                        $datum[self::FLAG_IS_REQUEST_DATA] = true;
                        $nestedData[] = $nestedClass::fromRequestData($datum);
                    } else {
                        $nestedData[] = new $nestedClass($datum);
                    }
                }

                $data[$property->getName()] = $nestedData;
            }
        }

        unset($data[self::FLAG_IS_REQUEST_DATA]);
        unset($data[self::FLAG_SKIP_VALIDATION]);

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

    public function __set(string $name, mixed $value): void
    {
        $this->__originalData[$name] = $value;
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
        return self::fromRequestData($request->input());
    }

    /**
     * Create DTO instance from existing model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return static
     */
    public static function fromModel(Model $model): static
    {
        $modelAttribute = self::getModelAttribute();

        if (null === $modelAttribute) {
            throw new \RuntimeException('No model defined for DTO');
        }

        $data = [];

        foreach (Property::collectFromClass(static::class) as $property) {
            $modelAttribute = $property->getModelAttribute();

            if (null === $modelAttribute) {
                continue;
            }

            $data[$property->getName()] = $model->{$modelAttribute};
        }

        $data[self::FLAG_SKIP_VALIDATION] = true;

        return new static($data);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return static
     */
    public static function fromRequestData(array $payload): static
    {
        $data = [];

        foreach (Property::collectFromClass(static::class) as $property) {
            $requestAttribute = $property->getRequestAttribute();

            if (null === $requestAttribute || ! isset($payload[$requestAttribute])) {
                continue;
            }

            $data[$property->getName()] = $payload[$requestAttribute];
        }

        $data[self::FLAG_IS_REQUEST_DATA] = true;

        return new static($data);
    }

    /**
     * Fill properties marked with #[ModelAttribute] to new model instanced declared in #[ForModel].
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function toModel(Model $model = null): Model
    {
        $modelClass = null;

        if ($attribute = self::getModelAttribute()) {
            $modelClass = $attribute->model;
        }

        if (null === $modelClass) {
            throw new \RuntimeException('No model defined for DTO');
        }

        $attributes = [];

        foreach (Property::collectFromClass(static::class) as $property) {
            $modelAttribute = $property->getModelAttribute();

            // There was no attribute set to cast to a model value or the value is missing
            if (null === $modelAttribute || ! $this->isset($property->getName())) {
                continue;
            }

            $value = $property->getConvertedValue(
                $this->{$property->getName()}
            );

            // Don't overwrite model attributes with default values.
            // We track if a value has been set intially or altered (via __set)
            if (null !== $model && $property->hasDefaultValue() && $property->getDefaultValue() === $value) {
                continue;
            }

            $attributes[$modelAttribute] = $value;
        }

        if (null === $model) {
            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $model = new $modelClass();
        }

        $model->fill($attributes);

        return $model;
    }

    private static function getModelAttribute(): ?ForModel
    {
        $reflectionClass = new \ReflectionClass(static::class);

        foreach ($reflectionClass->getAttributes(ForModel::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
