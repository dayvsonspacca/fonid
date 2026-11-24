<?php

declare(strict_types=1);

namespace Tests\Unit\PHPStan\Data;

// Not personal data: free to convert to string and expose properties.

final readonly class NotPersonalData
{
    public function __construct(
        public string $value,
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }
}
