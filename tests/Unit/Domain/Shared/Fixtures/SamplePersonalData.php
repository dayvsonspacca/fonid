<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\Fixtures;

use Fonid\Domain\Shared\PersonalData;

final readonly class SamplePersonalData extends PersonalData
{
    public function __construct(
        #[\SensitiveParameter]
        private string $value,
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    public function masked(): string
    {
        return '***'.substr($this->value, -2);
    }
}
