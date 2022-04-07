<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Validation\ValidationException;
use romanzipp\DTO\Attributes\Required;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\NestedModelData;
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

    public function testNestedValidation()
    {
        $data = new class(['items' => [['name' => 'Foo']]]) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertCount(1, $data->items);
        self::assertInstanceOf(ValidationNestedItem::class, $data->items[0]);
        self::assertSame('Foo', $data->items[0]->name);
    }

    public function testNestedValidationMultipleItems()
    {
        $data = new class(['items' => [['name' => 'Foo'], ['name' => 'Bar']]]) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertCount(2, $data->items);
        self::assertInstanceOf(ValidationNestedItem::class, $data->items[0]);
        self::assertSame('Foo', $data->items[0]->name);
        self::assertInstanceOf(ValidationNestedItem::class, $data->items[1]);
        self::assertSame('Bar', $data->items[1]->name);
    }

    public function testNestedValidationParentWrongType()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The items must be an array.');

        new class(['items' => 'Foo']) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };
    }

    public function testNestedValidationParentNull()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The items field is required.');

        new class(['items' => null]) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };
    }

    public function testNestedValidationParentMissing()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The items field is required.');

        new class([]) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };
    }

    public function testNestedValidationInvalidItem()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The name must be a string.');

        new class(['items' => [['name' => 123]]]) extends AbstractModelData {
            /**
             * @var \romanzipp\LaravelDTO\Tests\ValidationNestedItem[]
             */
            #[NestedModelData(ValidationNestedItem::class), ValidationRule(['required', 'array'])]
            public array $items;
        };
    }
}

class ValidationNestedItem extends AbstractModelData
{
    #[ValidationRule(['string'])]
    public string $name;
}
