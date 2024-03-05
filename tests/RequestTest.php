<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\NestedModelData;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

class RequestTest extends TestCase
{
    public function testFilledAttribute()
    {
        $data = RequestSampleData::fromRequest(
            Request::create('/', 'POST', ['some_name' => 'Foo'])
        );

        self::assertTrue($data->isset('name'));
        self::assertSame('Foo', $data->name);
    }

    public function testMissingAttribute()
    {
        $data = RequestSampleData::fromRequest(
            Request::create('/', 'POST', ['other_name' => 'Foo'])
        );

        self::assertFalse($data->isset('name'));
    }

    public function testRequestWithoutAttributeName()
    {
        $data = RequestSampleDataMissingRequestAttributeName::fromRequest(
            Request::create('/', 'POST', ['name' => 'Foo'])
        );

        self::assertTrue($data->isset('name'));
        self::assertSame('Foo', $data->name);
    }

    public function testRequestDataErrorKeyCorrect()
    {
        try {
            RequestSampleDataWithValidation::fromRequest(
                Request::create('/', 'POST', ['some_name' => null])
            );

            self::fail();
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('some_name', $exception->errors());
        }
    }

    public function testRequestDataNested()
    {
        $data = RequestSampleDataNested::fromRequest(
            Request::create('/', 'POST', [
                'parent_name' => 'Foo',
                'items' => [
                    [
                        'item_name' => 'Bar',
                    ],
                ],
            ])
        );

        self::assertInstanceOf(RequestSampleDataNested::class, $data);
    }

    public function testMultiStageNesting()
    {
        $data = RequestSampleDataMultiNest::fromRequest(
            Request::create('/', 'POST', [
                'first_items' => [
                    [
                        'second_items' => [
                            [
                                'last_name' => 'Foo',
                            ],
                        ],
                    ],
                ],
            ])
        );

        self::assertInstanceOf(RequestSampleDataMultiNest::class, $data);
        self::assertCount(1, $data->items);
        self::assertInstanceOf(RequestSampleDataMultiNestFirstItem::class, $data->items[0]);
        self::assertCount(1, $data->items[0]->items);
        self::assertInstanceOf(RequestSampleDataMultiNestSecondItem::class, $data->items[0]->items[0]);
        self::assertSame('Foo', $data->items[0]->items[0]->name);
    }
}

class RequestSampleData extends AbstractModelData
{
    #[RequestAttribute('some_name')]
    public string $name;
}

class RequestSampleDataMissingRequestAttributeName extends AbstractModelData
{
    #[RequestAttribute]
    public string $name;
}

class RequestSampleDataWithValidation extends AbstractModelData
{
    #[RequestAttribute('some_name'), ValidationRule(['required'])]
    public string $name;
}

class RequestSampleDataNested extends AbstractModelData
{
    #[RequestAttribute('parent_name'), ValidationRule(['required', 'string'])]
    public string $name;

    /**
     * @var \romanzipp\LaravelDTO\Tests\RequestSampleDataNestedItem[]
     */
    #[NestedModelData(RequestSampleDataNestedItem::class), ValidationRule(['required']), RequestAttribute]
    public array $items;
}

class RequestSampleDataNestedItem extends AbstractModelData
{
    #[RequestAttribute('item_name'), ValidationRule(['required', 'string'])]
    public string $name;
}

// Multi stage nesting

class RequestSampleDataMultiNest extends AbstractModelData
{
    /**
     * @var RequestSampleDataMultiNestFirstItem[]
     */
    #[NestedModelData(RequestSampleDataMultiNestFirstItem::class), RequestAttribute('first_items'), ValidationRule(['required', 'array'])]
    public array $items;
}

class RequestSampleDataMultiNestFirstItem extends AbstractModelData
{
    /**
     * @var RequestSampleDataMultiNestSecondItem[]
     */
    #[NestedModelData(RequestSampleDataMultiNestSecondItem::class), RequestAttribute('second_items'), ValidationRule(['required', 'array'])]
    public array $items;
}

class RequestSampleDataMultiNestSecondItem extends AbstractModelData
{
    #[RequestAttribute('last_name'), ValidationRule(['required', 'string'])]
    public string $name;
}
