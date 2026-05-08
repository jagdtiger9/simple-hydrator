<?php

namespace Aljerom\SimpleHydrator;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PostHydrate
{
    public function __construct(public ?string $methodName = null)
    {
    }
}
