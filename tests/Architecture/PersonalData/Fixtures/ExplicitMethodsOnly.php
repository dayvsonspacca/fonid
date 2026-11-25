<?php

declare(strict_types=1);

namespace Tests\Architecture\PersonalData\Fixtures;

use Fonid\Domain\Shared\PersonalData;

final readonly class ExplicitMethodsOnly extends PersonalData
{
    public function __construct(
        private string $value,
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    public function masked(): string
    {
        return '***';
    }
}
