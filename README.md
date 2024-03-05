# Laravel DTO

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-DTO.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-dto)
[![GitHub Build Status](https://img.shields.io/github/workflow/status/romanzipp/Laravel-DTO/Tests?style=flat-square)](https://github.com/romanzipp/Laravel-DTO/actions)

A strongly typed Data Transfer Object **for Laravel** without magic for PHP 8.0+

This package extends the functionality of [**romanzipp/DTO**](https://github.com/romanzipp/DTO) to provide more narrow usecases for Laravel applications.

Laravel-DTO serves as an **intermediate and reusable layer** between request input & validation and model attribute population.

## Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Validation](#validation)
  - [Hydrate models](#hydrate-models)
  - [**Combined usage**](#combined-usage)
  - [Validate arrays](#validate-arrays)
  - [Type casting: Arrays to DTOs](#cast-arrays-to-dtos-nested-data)
  - [Type casting](#type-casting)
  - [IDE Support](#ide-support)
- [Testing](#testing)

## Installation

```
composer require romanzipp/laravel-dto
```

## Usage

All data objects must extend the [`romanzipp\LaravelDTO\AbstractModelData`](src/AbstractModelData.php) class.

## Validation

When attaching the [`#[ValidationRule]`](src/Attributes/ValidationRule.php) any given data will be passed to the Laravel Validator so you can make use of all [available validation rules](https://laravel.com/docs/9.x/validation#available-validation-rules) and even built-in rules instances.

```php
use App\Models\Person;
use App\Models\Project;
use Illuminate\Validation\Rules\Exists;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ValidationRule;
use romanzipp\LaravelDTO\Attributes\ValidationChildrenRule;

class PersonData extends AbstractModelData
{
    #[ValidationRule(['required', 'string', 'min:1', 'max:255'])]
    public string $name;

    #[ModelAttribute(['sometimes', 'min:18'])] 
    public int $currentAge;

    #[ValidationRule(['nullable', 'string', 'in:de,en'])]
    public ?string $language;

    #[ValidationRule(['required', 'numeric', new Exists(Project::class, 'id')])]
    public int $projectId;
    
    #[ValidationRule(['required', 'array', 'min:1']), ValidationChildrenRule(['string'], '*.device'), ValidationChildrenRule(['ipv4'], '*.ip')]
    public array $logins;
}
```

This will throw a `Illuminate\Validation\ValidationException` if any rule does not pass.

```php
$data = new PersonData([
    'name' => 'John Doe',
    'currentAge' => 25,
    'language' => 'de',
    'projectId' => 2,
    'logins' => [
        ['device' => 'PC', 'ip' => '85.120.61.36'],
        ['device' => 'iOS', 'ip' => '85.120.61.36'],
    ]
]);
```

## Hydrate Models

You can attach a model to any DTO using the [`#[ForModel(Model::class)]`](src/Attributes/ForModel.php) attribute.
To associate DTO properties with Model attributes, you need to attach the [`#[ModelAttribute()]`](src/Attributes/ModelAttribute.php) attribute to each property.
If no parameter is passed to the [`#[ModelAttribute]`](src/Attributes/ModelAttribute.php) attribute, DTO uses the property name itself.

```php
use App\Models\Person;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;

#[ForModel(Person::class)]
class PersonData extends AbstractModelData
{
    #[ModelAttribute]                 // The `$name` DTO property will populate the `name` model attribute
    public string $name;

    #[ModelAttribute('current_age')]  // The `$currentAge` DTO property will populate the `current_age` model attribute
    public int $currentAge;

    public string $language;          // The `$language` DTO property will be ignored
}
```

**Create DTO and store to model**

```php
$data = new PersonData([
    'name' => 'John Doe',
    'currentAge' => 25,
    'language' => 'de',
]);

$person = $data->toModel()->save();
```

**Attributes saved in `Person` model**

```json
{
    "name": "John Doe",
    "current_age": 25
}
```

**Note**: You can also pass an existing model to the `toModel()` method.

```php
use App\Models\Person;

$person = $data->toModel($person)->save();
```

**Note**: When passing **no** existing model to the `toModel()` method, default values declared in the DTO will be populated. If a model is passed as argument `toModel($model)` default values will not override existing model attributes.

### Populate DTO from request input data

When attaching the [`#[RequestAttribute]`](src/Attributes/RequestAttribute.php) and creating a DTO instance via the `fromRequest(Request $request)` method all matching attributes will be populated by the input data. If no parameter is passed to the [`#[RequestAttribute]`](src/Attributes/RequestAttribute.php) attribute, DTO uses the property name itself.

```php
use App\Models\Person;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;

#[ForModel(Person::class)]
class PersonData extends AbstractModelData
{
    #[RequestAttribute]            // The `$name` DTO property will de populated by the `name` request attribute
    public string $name;

    #[RequestAttribute('my_age')]  // The `$currentAge` DTO property will be populated by `my_age` request attribute
    public int $currentAge;

    public string $language;       // The `$language` DTO property will not be populated
}
```

**The controller**

```php
use App\Data\PersonData;
use Illuminate\Http\Request;

class TestController
{
    public function store(Request $request)
    {
        $data = PersonData::fromRequest($request);
    }
}
```

**Request input data**

```json
{
  "name": "John Doe",
  "my_age": 25,
  "language": "de"
}
```

**The `PersonData` DTO instance**

```
App\Data\PersonData^ {
  +name: "John Doe"
  +currentAge: 25
}
```

## Combined usage

Of course all those attributes start to make sense if used together. You can attach all attributes separately of make use of the [`#[ValidatedRequestModelAttribute]`](src/Attributes/ValidatedRequestModelAttribute.php) attribute which combines the functionality of all [`#[RequestAttribute]`](src/Attributes/RequestAttribute.php), [`#[ModelAttribute]`](src/Attributes/ModelAttribute.php) and [`#[ValidationRule]`](src/Attributes/ValidationRule.php) attributes.

Both properties in the following example behave exactly the same. Use as you prefer.

```php
use App\Models\Person;
use Illuminate\Validation\Rules\Exists;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidatedRequestModelAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

#[ForModel(Person::class)]
class PersonData extends AbstractModelData
{
    // All attributes attached separately (looks disgusting doesn't it?)
    #[
        ValidationRule(['required', 'numeric', 'min:18']),
        RequestAttribute('my_age'),
        ModelAttribute('current_age')
    ]
    public string $currentAge;

    // Combined usage
    // The `my_age` request attribute will be validated and set to the `current_age` model attribute.
    #[ValidatedRequestModelAttribute(['required', 'numeric', 'min:18'], 'my_age', 'current_age')]
    public string $currentAge;
}
```

**Request input data**

```json
{
  "my_age": 25
}
```

**The controller**

```php
use App\Data\PersonData;
use Illuminate\Http\Request;

class TestController
{
    public function index(Request $request)
    {
        $person = PersonData::fromRequest($request)->toModel()->save();

        return $person->id;
    }
}
```

## Validate arrays

If you only want to validate an array without casting the children items to another DTO, you can make use of the `ValidationChildrenRule` attribute.

The first parameter to the `ValidationChildrenRule` attribute is the validation rule for the children items. The second parameter is the validator path to access the children key to validate.

#### Validate a simple array with numeric indexes

```php
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidationChildrenRule;

$data = [
    'logins' => [
        '127.0.0.1',
        '127.0.0.1'
    ]
];

class PersonData extends AbstractModelData
{
    #[ValidationChildrenRule(['string', 'ipv4'], '*')];
    public array $logins;
}
```

#### Validate associative arrays with named keys

```php
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidationChildrenRule;

$data = [
    'logins' => [
        ['ip' => '127.0.0.1'],
        ['ip' => '127.0.0.1']
    ]
];

class PersonData extends AbstractModelData
{
    #[ValidationChildrenRule(['string', 'ipv4'], '*.ip')];
    public array $logins;
}
```

#### Multiple validation rules

```php
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidationChildrenRule;

$data = [
    'logins' => [
        ['ip' => '127.0.0.1', 'device' => 'iOS'],
        ['ip' => '127.0.0.1', 'device' => 'macOS']
    ]
];

class PersonData extends AbstractModelData
{
    #[
        ValidationChildrenRule(['string', 'ipv4'], '*.ip'),
        ValidationChildrenRule(['string'], '*.device')
    ];
    public array $logins;
}
```

## Cast arrays to DTOs (Nested data)

In some cases you also want to create realted models with a single HTTP call. In this case you can make use of the [`#[NestedModelData(NestedData::class)]`](src/Attributes/NestedModelData.php) which will populate the DTO property with n instances of the defined DTO. 

Note that we will not attach an [`#[ModelAttribute]`](src/Attributes/ModelAttribute.php) attribute to the `$address` DTO property since it should not be set to a model attribute.

All attributes attached to the nested DTO will just work as expected.

```php
use App\Models\Person;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\NestedModelData;
use romanzipp\LaravelDTO\Attributes\RequestAttribute;
use romanzipp\LaravelDTO\Attributes\ValidatedRequestModelAttribute;
use romanzipp\LaravelDTO\Attributes\ValidationRule;

#[ForModel(Person::class)]
class PersonData extends AbstractModelData
{
    #[ValidatedRequestModelAttribute(['required', 'string'])]
    public string $name;

    /**
     * @var AddressData[] 
     */
    #[NestedModelData(AddressData::class), ValidationRule(['required', 'array']), RequestAttribute]
    public array $adresses;
}
```

```php
use App\Models\Address;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ValidatedRequestModelAttribute;

#[ForModel(Address::class)]
class AddressData extends AbstractModelData
{
    #[ValidatedRequestModelAttribute(['string'])]
    public string $street;

    #[ValidatedRequestModelAttribute(['nullable', 'int'])]
    public ?int $apartment = null;
}
```

**Request input data**

```json
{
  "name": "John Doe",
  "addresses": [
    {
      "street": "Sample Street"
    },
    {
      "street": "Debugging Alley",
      "apartment": 43
    }
  ]
}
```

**The controller**

```php
use App\Data\PersonData;
use Illuminate\Http\Request;

class TestController
{
    public function index(Request $request)
    {
        $personData = PersonData::fromRequest($request);
        $person = $personData->toModel()->save();

        foreach ($personData->addresses as $addressData) {
            // We assume the `Person` model has a has-many relation with the `Address` model
            $person->addresses()->save(
                $addressData->toModel()
            );
        }

        return $person->id;
    }
}
```

## Type Casting

Type casts will convert any given value to a specified type.

### Built-in type casts

#### [`CastToDate`](src/Attributes/Casts/CastToDate.php)

The [`#[CastToDate]`](src/Attributes/Casts/CastToDate.php) attribute will respect your customly defined date class from `Date::use(...)`.
You can also specify a custom date class to be used by passing the date class name as single argument [`#[CastToDate(MyDateClass::class)]`](src/Attributes/Casts/CastToDate.php).

```php
use Carbon\Carbon;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\Casts\CastToDate;

class PersonData extends AbstractModelData
{
    #[CastToDate]
    public Carbon $date;
}
```

### Custom type casts

You can declare custom type cast attributes by simply implementing the [`CastInterface`](src/Attributes/Casts/CastInterface.php) interface and attaching an attribute.

```php
use Attribute;
use romanzipp\LaravelDTO\Attributes\Casts\CastInterface;

#[Attribute]
class MyCast implements CastInterface
{
    public function castToType(mixed $value): mixed
    {
        return (string) $value;
    }
}
```

## IDE Support

Make sure to add a `@method` PHPDoc comment like shown below to allow IDE and static analyzer support when calling the `toModel()` method.

```php
use App\Models\Person;
use romanzipp\LaravelDTO\AbstractModelData;
use romanzipp\LaravelDTO\Attributes\ForModel;
use romanzipp\LaravelDTO\Attributes\ModelAttribute;

/**
 * @method Person toModel()
 */
#[ForModel(Person::class)]
class PersonData extends AbstractModelData
{
    #[ModelAttribute]
    public string $name;
}
```

## Testing

### PHPUnit

```
./vendor/bin/phpunit
```

### PHPStan

```
./vendor/bin/phpstan
```

## Authors

- [Roman Zipp](https://github.com/romanzipp)
