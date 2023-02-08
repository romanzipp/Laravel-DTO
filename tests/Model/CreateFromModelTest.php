<?php

namespace romanzipp\LaravelDTO\Tests\Model;

use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;
use romanzipp\LaravelDTO\Tests\TestCase;

class CreateFromModelTest extends TestCase
{
    public function testCanNotCreateFromModelIfMissingForModelAttribute()
    {
        $this->expectException(\RuntimeException::class);

        $model = new SampleModel([]);

        CreateFromModelDataMissingAttribute::fromModel($model);
    }

    public function testCreateFromModel()
    {
        $model = new SampleModel([
            'name' => 'Bar',
            'display_name' => 'Foo',
        ]);

        $data = CreateFromModelData::fromModel($model);

        self::assertInstanceOf(CreateFromModelData::class, $data);
        self::assertSame('Bar', $data->name);
        self::assertSame('Foo', $data->displayName);
    }
}
class CreateFromModelDataMissingAttribute extends AbstractModelData
{
}

#[ForModel(SampleModel::class)]
class CreateFromModelData extends AbstractModelData
{
    #[ModelAttribute('name')]
    public string $name;

    #[ModelAttribute('display_name'), ValidationRule(['string', 'min:20'])]
    public string $displayName;
}
