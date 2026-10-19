<?php

namespace Aljerom\SimpleHydrator\Tests\Fixture;

use Aljerom\SimpleHydrator\Setter;

#[Setter]
class SetterDto
{
    public string $title;
    public array $tags;

    public function setTags(string $value): void
    {
        $this->tags = explode(',', $value);
    }
}
