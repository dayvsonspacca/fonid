<?php

declare(strict_types=1);

namespace Tests\Unit\PHPStan\Data;

use Fonid\Domain\Shared\PersonalData;

final readonly class ConvertsToJson extends PersonalData implements \JsonSerializable
{
    public function __construct(
        private string $value,
    ) {}

    public function masked(): string
    {
        return '***';
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
