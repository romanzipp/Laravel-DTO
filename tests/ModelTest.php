<?php

namespace romanzipp\LaravelDTO\Tests;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;
use romanzipp\LaravelDTO\Tests\Support\SampleModel;

class ModelTest extends TestCase
{
    public function testModelAttribute()
    {
        $data = new #[ForModel(SampleModel::class)] class(['name' => 'Roman']) extends AbstractModelData {
            #[ValidationRule(['required'])]
            #[ModelAttribute('name')]
            public string $name;
        };

        $model = $data->makeModel();

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(SampleModel::class, $model);
    }
}
