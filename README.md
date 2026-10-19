# SimpleHydrator

Reflection-based array-to-object hydrator with `snake_case` to `camelCase` key mapping.

## Installation

```bash
composer require aljerom/simple-hydrator
```

## Usage

```php
use Aljerom\SimpleHydrator\SimpleHydrator;

class AddressDto
{
    public string $city;
    public string $country;
}

class UserDto
{
    public string $name;
    public DateTimeInterface $createdAt;
    public array $tags;
    public AddressDto $address;
}

$data = [
    'name' => 'Alice',
    'created_at' => '2024-01-01 00:00:00',
    'tags' => '["php","oop"]', // JSON strings are decoded
    'address' => ['city' => 'Berlin', 'country' => 'DE'], // nested objects are hydrated recursively
];

$user = (new SimpleHydrator())->hydrate($data, UserDto::class);
```

## Behavior

- Instantiates the target class **without calling its constructor** (promoted property defaults are preserved as a workaround for [php#81386](https://bugs.php.net/bug.php?id=81386)).
- Converts `snake_case` (and `UpperCamelCase`) keys to `camelCase` property names.
- Converts strings to `DateTimeImmutable` for `DateTimeInterface` properties; `null` becomes epoch `0`, nullable properties accept `null`.
- Decodes JSON strings for `array` properties.
- Hydrates class-typed properties recursively.
- `null` passed for a non-nullable property keeps the property's default value.
- Unknown keys in the input array are ignored.

## Requirements

- PHP >= 8.3

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT
