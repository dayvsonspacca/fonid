<?php

declare(strict_types=1);

namespace Fonid\Domain\Shared;

/**
 * Base for every value object that holds personal data.
 *
 * The value only comes out through explicit methods on the subclass. There is
 * deliberately no __toString and no jsonSerialize: without them, casts and
 * interpolation fail static analysis. Serialization throws, and dumps show
 * only the masked form.
 *
 * @see docs/adr/202611241754-allow-personal-data-out-only-through-explicit-calls.md
 */
abstract readonly class PersonalData
{
    /**
     * The value with most of it hidden, safe for logs.
     */
    abstract public function masked(): string;

    /**
     * @return array{masked: string}
     */
    final public function __debugInfo(): array
    {
        return ['masked' => $this->masked()];
    }

    /**
     * @return array<string, mixed>
     */
    final public function __serialize(): array
    {
        throw PersonalDataCannotBeSerialized::for(static::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    final public function __unserialize(array $data): void
    {
        throw PersonalDataCannotBeSerialized::for(static::class);
    }
}
