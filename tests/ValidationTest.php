<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Validation\ValidationException;
use romanzipp\DTO\Attributes\Required;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidatesInput;

class ValidationTest extends TestCase
{
    public function testRequired()
    {
        $data = new class(['name' => 'Roman']) extends AbstractModelData {
            #[ValidatesInput(['required'])]
            public string $name;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
    }

    public function testRequiredMissing()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');

        new class([]) extends AbstractModelData {
            #[ValidatesInput(['required'])]
            public string $name;
        };
    }

    public function testRequiredNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is required.');

        new class(['name' => null]) extends AbstractModelData {
            #[ValidatesInput(['required'])]
            public string $name;
        };
    }

    public function testValidationSucceededWithInvalidDataException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name field is invalid.');

        new class(['name' => null]) extends AbstractModelData {
            #[ValidatesInput([])]
            #[Required]
            public string $name;
        };
    }
}
