<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Validation\ValidationException;
use romanzipp\DTO\Attributes\Required;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

class ValidationTest extends TestCase
{
    public function testRequired()
    {
        $data = new class(['name' => 'Roman']) extends AbstractModelData {
            #[ValidationRule(['required'])]
            public string $name;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
    }

    public function testRequiredMissing()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');

        new class([]) extends AbstractModelData {
            #[ValidationRule(['required'])]
            public string $name;
        };
    }

    public function testRequiredNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');

        new class(['name' => null]) extends AbstractModelData {
            #[ValidationRule(['required'])]
            public string $name;
        };
    }

    public function testInteger()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The age must be at least 18.');

        new class(['age' => 5]) extends AbstractModelData {
            #[ValidationRule(['numeric', 'min:18'])]
            public string $age;
        };
    }

    public function testValidationSucceededWithInvalidDataException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is invalid.');

        new class(['name' => null]) extends AbstractModelData {
            #[ValidationRule([])]
            #[Required]
            public string $name;
        };
    }
}
