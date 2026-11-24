<?php

declare(strict_types=1);

namespace Tests\Unit\PHPStan\Data;

use Fonid\Domain\Shared\PersonalData;

final readonly class Clean extends PersonalData
{
    public function __construct(
        private string $value,
    ) {}

    public function masked(): string
    {
        return '***';
    }
}
