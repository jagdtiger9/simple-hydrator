<?php

namespace Aljerom\SimpleHydrator\Tests\Fixture;

class PromotedDto
{
    public function __construct(
        public string $name = 'default',
        public int $page = 1,
    ) {
    }
}
