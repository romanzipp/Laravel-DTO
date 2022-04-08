<?php

namespace romanzipp\LaravelDTO\Tests;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Tests\Support\SampleModel;

class ModelTest extends TestCase
{
    public function testModelAttribute()
    {
        $data = new #[ForModel(SampleModel::class)] class(['name' => 'Roman']) extends AbstractModelData {
            #[ModelAttribute('name')]
            public string $name;
        };

        $model = $data->toModel();

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(SampleModel::class, $model);
        self::assertSame('Roman', $model->name);
    }

    public function testModelAttributeWithoutAttributeName()
    {
        $data = new #[ForModel(SampleModel::class)] class(['name' => 'Roman']) extends AbstractModelData {
            #[ModelAttribute]
            public string $name;
        };

        $model = $data->toModel();

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(SampleModel::class, $model);
        self::assertSame('Roman', $model->name);
    }

    public function testModelAttributeMissing()
    {
        $data = new #[ForModel(SampleModel::class)] class() extends AbstractModelData {
            #[ModelAttribute('name')]
            public string $name;
        };

        $model = $data->toModel();

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(SampleModel::class, $model);
        self::assertNull($model->name);
    }

    public function testExistingModelAtteribute()
    {
        $model = new SampleModel([
            'name' => 'Foo',
            'age' => 18,
        ]);

        self::assertSame('Foo', $model->name);
        self::assertSame(18, $model->age);

        $data = new #[ForModel(SampleModel::class)] class(['name' => 'Bar']) extends AbstractModelData {
            #[ModelAttribute('name')]
            public string $name;
        };

        $model = $data->toModel($model);

        self::assertSame('Bar', $model->name);
        self::assertSame(18, $model->age);
    }
}
