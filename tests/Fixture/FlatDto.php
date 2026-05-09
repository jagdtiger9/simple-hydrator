<?php

namespace Aljerom\SimpleHydrator\Tests\Fixture;

use DateTimeInterface;

class FlatDto
{
    public string $name;
    public int $count;
    public float $price;
    public bool $active;
    public ?string $description;
    public array $tags;
    public DateTimeInterface $createdAt;
    public ?DateTimeInterface $deletedAt;
}
