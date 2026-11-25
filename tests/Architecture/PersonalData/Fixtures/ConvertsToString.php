<?php

declare(strict_types=1);

namespace Tests\Architecture\PersonalData\Fixtures;

use Fonid\Domain\Shared\PersonalData;

final readonly class ConvertsToString extends PersonalData
{
    public function __construct(
        private string $value,
    ) {}

    public function masked(): string
    {
        return '***';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
