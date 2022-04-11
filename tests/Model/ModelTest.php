<?php

namespace romanzipp\LaravelDTO\Tests\Model;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Tests\TestCase;

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

    public function testExistingModelAttribute()
    {
        $model = new SampleModel([
            'first' => 'Foo',
            'second' => 18,
        ]);

        self::assertSame('Foo', $model->first);
        self::assertSame(18, $model->second);
        self::assertArrayNotHasKey('third', $model->getAttributes());

        $data = new #[ForModel(SampleModel::class)] class(['first' => 'Bar', 'third' => null]) extends AbstractModelData {
            #[ModelAttribute]
            public string $first;

            #[ModelAttribute]
            public ?string $third;

            #[ModelAttribute]
            public ?string $fourth;

            public ?string $fifth;
        };

        $model = $data->toModel($model);

        self::assertSame('Bar', $model->first);
        self::assertSame(18, $model->second);

        self::assertArrayHasKey('third', $model->getAttributes());
        self::assertArrayNotHasKey('fourth', $model->getAttributes());
        self::assertArrayNotHasKey('fifth', $model->getAttributes());
    }

    public function testModelNotOverwrittenWithDefaultDataProperties()
    {
        $model = new SampleModel([
            'first' => 'Foo',
            'hasKoeder' => true,
        ]);

        $data = new #[ForModel(SampleModel::class)] class(['first' => 'Bar']) extends AbstractModelData {
            #[ModelAttribute]
            public string $first;

            #[ModelAttribute]
            public bool $hasKoeder = false;
        };

        $model = $data->toModel($model);

        self::assertSame(true, $model->hasKoeder);
    }
}
