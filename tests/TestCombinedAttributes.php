<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Http\Request;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;
use romanzipp\LaravelDTO\Tests\Support\SampleModel;

class TestCombinedAttributes extends TestCase
{
    public function testCombined()
    {
        $data = CombinedRequestSampleData::fromRequest(
            Request::create('/', 'POST', ['some_name' => 'Foo'])
        );

        $model = $data->makeModel();

        self::assertInstanceOf(SampleModel::class, $model);
        self::assertSame('Foo', $model->other_name);
    }
}

#[ForModel(SampleModel::class)]
class CombinedRequestSampleData extends AbstractModelData
{
    #[RequestAttribute('some_name'), ModelAttribute('other_name'), ValidationRule(['required', 'string'])]
    public string $name;
}
