<?php

namespace romanzipp\LaravelDTO\Tests;

use Illuminate\Http\Request;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;

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

    public function testMissingRequestAttributeName()
    {
        $data = RequestSampleDataMissingRequestAttributeName::fromRequest(
            Request::create('/', 'POST', ['name' => 'Foo'])
        );

        self::assertTrue($data->isset('name'));
        self::assertSame('Foo', $data->name);
    }
}

class RequestSampleData extends AbstractModelData
{
    #[RequestAttribute('some_name')]
    public string $name;
}

class RequestSampleDataMissingRequestAttributeName extends AbstractModelData
{
    #[RequestAttribute()]
    public string $name;
}
