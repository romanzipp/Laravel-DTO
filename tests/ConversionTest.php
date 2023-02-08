<?php

namespace romanzipp\LaravelDTO\Tests;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\Conversions\ConvertsFromEnum;
use romanzipp\LaravelDTO\Attributes\Conversions\ConvertsToJson;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Tests\Enums\IntegerEnum;
use romanzipp\LaravelDTO\Tests\Model\SampleModel;

class ConversionTest extends TestCase
{
    public function testEnumConvertedToValue()
    {
        $data = new #[ForModel(SampleModel::class)] class(['foo' => IntegerEnum::FOO]) extends AbstractModelData {
            #[ModelAttribute('foo'), ConvertsFromEnum]
            public IntegerEnum $foo;
        };

        $model = $data->toModel();

        self::assertSame($data->foo, IntegerEnum::FOO);
        self::assertSame(1, $model->foo);
    }

    public function testEnumConvertedToValueWithoutAttribute()
    {
        $data = new #[ForModel(SampleModel::class)] class(['foo' => IntegerEnum::FOO]) extends AbstractModelData {
            #[ModelAttribute('foo')]
            public IntegerEnum $foo;
        };

        $model = $data->toModel();

        self::assertSame($data->foo, IntegerEnum::FOO);
        self::assertSame(1, $model->foo);
    }

    public function testConvertToJson()
    {
        $data = new #[ForModel(SampleModel::class)] class(['foo' => ['foo' => 'bar']]) extends AbstractModelData {
            #[ModelAttribute('foo'), ConvertsToJson]
            public array $foo;
        };

        $model = $data->toModel();

        self::assertSame(['foo' => 'bar'], $data->foo);
        self::assertSame('{"foo":"bar"}', $model->foo);
    }
}
