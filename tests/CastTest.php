<?php

namespace romanzipp\LaravelDTO\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\Casts\CastToDate;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

class CastTest extends TestCase
{
    public function testDateCast()
    {
        $data = new class(['date' => (string) Carbon::now()]) extends AbstractModelData {
            #[ValidationRule(['date']), CastToDate]
            public Carbon $date;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(Carbon::class, $data->date);
    }

    public function testDateCastWithType()
    {
        $data = new class(['date' => (string) Carbon::now()]) extends AbstractModelData {
            #[ValidationRule(['date']), CastToDate(CarbonImmutable::class)]
            public CarbonImmutable $date;
        };

        self::assertInstanceOf(AbstractModelData::class, $data);
        self::assertInstanceOf(CarbonImmutable::class, $data->date);
    }

    public function testDateCastFromRequest()
    {
        $data = DateCastRequestData::fromRequest(
            Request::create('/', 'POST', ['starts_at' => (string) Carbon::now()])
        );

        self::assertTrue($data->isset('date'));
        self::assertInstanceOf(Carbon::class, $data->date);
    }
}

class DateCastRequestData extends AbstractModelData
{
    #[RequestAttribute('starts_at'), CastToDate]
    public Carbon $date;
}
