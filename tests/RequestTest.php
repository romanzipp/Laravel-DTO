<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use romanzipp\LaravelDTO\AbstractModelData;
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
