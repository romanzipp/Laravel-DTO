# Laravel DTO

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![GitHub Build Status](https://img.shields.io/github/workflow/status/romanzipp/Laravel-DTO/Tests?style=flat-square)](https://github.com/romanzipp/Laravel-DTO/actions)

A strongly typed Data Transfer Object **for Laravel** without magic for PHP 8.0+

This package extends the functionality of [**romanzipp/DTO**](https://github.com/romanzipp/DTO) to provide more narrow usecases for Laravel applications.

## Contents

- [Installation](#installation)
- [Usage](#usage)

## Installation

```
composer require romanzipp/laravel-dto
```

## Usage

```php
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;

#[ForModel(SampleModel::class)]
class MyModelData extends AbstractModelData
{
    #[RequestAttribute('first_name'), ModelAttribute, ValidationRule(['required'])]
    public string $name;

    #[RequestAttribute, ModelAttribute, ValidationRule(['required', 'numeric'])]
    public string $age;
}
```

```php
use Illuminate\Http\Request;

class TestController
{
    public function index(Request $request)
    {
        $model = MyModelData::fromRequest($request)->toModel()->create();

        return $model->id;
    }
}
```

## Testing

```
./vendor/bin/phpunit
```

## Authors

- [Roman Zipp](https://github.com/romanzipp)
