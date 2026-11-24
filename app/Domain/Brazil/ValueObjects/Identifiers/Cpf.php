<?php

declare(strict_types=1);

namespace Fonid\Domain\Brazil\ValueObjects\Identifiers;

use Fonid\Domain\Brazil\Exceptions\InvalidCpf;
use Fonid\Domain\Shared\PersonalData;

/**
 * Brazilian individual taxpayer number (Cadastro de Pessoas Físicas).
 */
final readonly class Cpf extends PersonalData
{
    private function __construct(
        private string $digits,
    ) {}

    /**
     * Accepts 11 digits (52998224725) or the full mask (529.982.247-25),
     * with surrounding whitespace.
     *
     * @throws InvalidCpf
     */
    public static function from(#[\SensitiveParameter] string $value): self
    {
        $value = trim($value);

        if (preg_match('/^(?:\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})$/', $value) !== 1) {
            throw new InvalidCpf;
        }

        $digits = str_replace(['.', '-'], replace: '', subject: $value);

        if (preg_match('/^(\d)\1{10}$/', $digits) === 1 || ! self::hasValidCheckDigits($digits)) {
            throw new InvalidCpf;
        }

        return new self($digits);
    }

    /**
     * The 11 digits, without the mask.
     */
    public function value(): string
    {
        return $this->digits;
    }

    public function formatted(): string
    {
        return sprintf(
            '%s.%s.%s-%s',
            substr($this->digits, offset: 0, length: 3),
            substr($this->digits, offset: 3, length: 3),
            substr($this->digits, offset: 6, length: 3),
            substr($this->digits, offset: 9, length: 2),
        );
    }

    public function masked(): string
    {
        return sprintf(
            '***.%s.%s-**',
            substr($this->digits, offset: 3, length: 3),
            substr($this->digits, offset: 6, length: 3),
        );
    }

    public function equals(self $other): bool
    {
        return $this->digits === $other->digits;
    }

    private static function hasValidCheckDigits(string $digits): bool
    {
        return (
            self::checkDigit(substr($digits, offset: 0, length: 9)) === (int) $digits[9]
            && self::checkDigit(substr($digits, offset: 0, length: 10)) === (int) $digits[10]
        );
    }

    /**
     * Modulo 11 check digit: weights count down from the length plus one to 2.
     */
    private static function checkDigit(string $base): int
    {
        $sum = 0;
        $weight = strlen($base) + 1;

        foreach (str_split($base) as $digit) {
            $sum += (int) $digit * $weight--;
        }

        $remainder = ($sum * 10) % 11;

        return $remainder === 10 ? 0 : $remainder;
    }
}
