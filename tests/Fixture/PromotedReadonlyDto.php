<?php

namespace Aljerom\SimpleHydrator\Tests\Fixture;

class PromotedReadonlyDto
{
    public function __construct(
        public readonly string $name = 'default',
        public readonly int $page = 1,
    ) {
    }
}
