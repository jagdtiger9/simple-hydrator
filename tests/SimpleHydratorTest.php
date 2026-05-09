<?php

namespace Aljerom\SimpleHydrator\Tests;

use Aljerom\SimpleHydrator\SimpleHydrator;
use Aljerom\SimpleHydrator\Tests\Fixture\AddressDto;
use Aljerom\SimpleHydrator\Tests\Fixture\FlatDto;
use Aljerom\SimpleHydrator\Tests\Fixture\NestedDto;
use Aljerom\SimpleHydrator\Tests\Fixture\NonNullableWithDefaultDto;
use Aljerom\SimpleHydrator\Tests\Fixture\PromotedDto;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class SimpleHydratorTest extends TestCase
{
    private SimpleHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new SimpleHydrator();
    }

    public function testHydratesScalarProperties(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'Alice',
            'count' => 5,
            'price' => 9.99,
            'active' => true,
            'tags' => ['a', 'b'],
            'createdAt' => '2024-01-01 00:00:00',
        ], FlatDto::class);

        self::assertInstanceOf(FlatDto::class, $dto);
        self::assertSame('Alice', $dto->name);
        self::assertSame(5, $dto->count);
        self::assertSame(9.99, $dto->price);
        self::assertTrue($dto->active);
        self::assertSame(['a', 'b'], $dto->tags);
    }

    public function testConvertsSnakeCaseKeysToCamelCase(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => '2024-06-01 12:00:00',
        ], FlatDto::class);

        self::assertInstanceOf(DateTimeInterface::class, $dto->createdAt);
        self::assertSame('2024-06-01', $dto->createdAt->format('Y-m-d'));
    }

    public function testConvertsUpperCamelCaseKeyToLowerFirst(): void
    {
        $dto = $this->hydrator->hydrate([
            'Name' => 'Bob',
            'count' => 0,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'createdAt' => '2024-01-01 00:00:00',
        ], FlatDto::class);

        self::assertSame('Bob', $dto->name);
    }

    public function testHydratesDateTimeInterfaceFromString(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => '2023-03-15 08:30:00',
        ], FlatDto::class);

        self::assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        self::assertSame('2023-03-15', $dto->createdAt->format('Y-m-d'));
        self::assertSame('08:30:00', $dto->createdAt->format('H:i:s'));
    }

    public function testHydratesDateTimeInterfaceFromNullAsEpoch(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => null,
        ], FlatDto::class);

        self::assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        self::assertSame(0, $dto->createdAt->getTimestamp());
    }

    public function testNullableDateTimeAcceptsNull(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => '2024-01-01 00:00:00',
            'deleted_at' => null,
        ], FlatDto::class);

        self::assertNull($dto->deletedAt);
    }

    public function testHydratesArrayFromJsonString(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => '["php","oop"]',
            'created_at' => '2024-01-01 00:00:00',
        ], FlatDto::class);

        self::assertSame(['php', 'oop'], $dto->tags);
    }

    public function testHydratesArrayFromArray(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => ['php', 'oop'],
            'created_at' => '2024-01-01 00:00:00',
        ], FlatDto::class);

        self::assertSame(['php', 'oop'], $dto->tags);
    }

    public function testHydratesNestedObject(): void
    {
        $dto = $this->hydrator->hydrate([
            'title' => 'HQ',
            'address' => ['city' => 'Berlin', 'country' => 'DE'],
        ], NestedDto::class);

        self::assertInstanceOf(AddressDto::class, $dto->address);
        self::assertSame('Berlin', $dto->address->city);
        self::assertSame('DE', $dto->address->country);
    }

    public function testKeepsExistingValueWhenNullPassedForNonNullableProperty(): void
    {
        $dto = $this->hydrator->hydrate([
            'status' => null,
            'retries' => null,
        ], NonNullableWithDefaultDto::class);

        self::assertSame('active', $dto->status);
        self::assertSame(3, $dto->retries);
    }

    public function testPromotedPropertyDefaultsAreApplied(): void
    {
        $dto = $this->hydrator->hydrate([], PromotedDto::class);

        self::assertSame('default', $dto->name);
        self::assertSame(1, $dto->page);
    }

    public function testPromotedPropertyDefaultIsOverriddenByData(): void
    {
        $dto = $this->hydrator->hydrate(['name' => 'override', 'page' => 5], PromotedDto::class);

        self::assertSame('override', $dto->name);
        self::assertSame(5, $dto->page);
    }

    public function testUnknownKeysInDataAreIgnored(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => '2024-01-01 00:00:00',
            'no_such_field' => 'should be ignored',
        ], FlatDto::class);

        self::assertInstanceOf(FlatDto::class, $dto);
    }

    public function testNullableStringAcceptsNull(): void
    {
        $dto = $this->hydrator->hydrate([
            'name' => 'x',
            'count' => 1,
            'price' => 0.0,
            'active' => false,
            'tags' => [],
            'created_at' => '2024-01-01 00:00:00',
            'description' => null,
        ], FlatDto::class);

        self::assertNull($dto->description);
    }
}
