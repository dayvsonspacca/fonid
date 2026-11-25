<?php

declare(strict_types=1);

namespace Fonid\Domain\Brazil\Validation;

final class CpfValidator
{
    public static function isValid(#[\SensitiveParameter] string $value): bool
    {
        $value = trim($value);

        if (preg_match('/^(?:\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})$/', $value) !== 1) {
            return false;
        }

        $digits = str_replace(['.', '-'], replace: '', subject: $value);

        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        return (
            self::checkDigit(substr($digits, offset: 0, length: 9)) === (int) $digits[9]
            && self::checkDigit(substr($digits, offset: 0, length: 10)) === (int) $digits[10]
        );
    }

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
