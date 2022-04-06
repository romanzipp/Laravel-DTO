<?php

namespace romanzipp\LaravelDTO;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use romanzipp\DTO\AbstractData;
use romanzipp\DTO\Exceptions\InvalidDataException;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

abstract class AbstractModelData extends AbstractData
{
    private const INTERNAL_ATTRIBUTES = [
        ValidationRule::class,
    ];

    /**
     * @param array<string, mixed> $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $data = [])
    {
        $reflectionClass = new \ReflectionClass(static::class);

        $validationRules = [];
        $validationData = [];

        foreach ($reflectionClass->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if ( ! in_array($attribute->getName(), self::INTERNAL_ATTRIBUTES)) {
                    continue;
                }

                $name = $property->getName();

                /** @var \romanzipp\LaravelDTO\Attributes\ValidationRule $validation */
                $validation = $attribute->newInstance();

                $validationRules[$name] = $validation->rules;
                $validationData[$name] = $data[$name] ?? null;
            }
        }

         #dd($validationData,$validationRules);

        $validator = Validator::make($validationData, $validationRules);
        $validator->validate();

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
}
