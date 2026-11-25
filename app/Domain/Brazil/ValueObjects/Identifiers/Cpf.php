<?php

declare(strict_types=1);

namespace Fonid\Domain\Brazil\ValueObjects\Identifiers;

use Fonid\Domain\Brazil\Exceptions\InvalidCpf;
use Fonid\Domain\Brazil\Validation\CpfValidator;
use Fonid\Domain\Shared\PersonalData;

final readonly class Cpf extends PersonalData
{
    private function __construct(
        private string $digits,
    ) {}

    /**
     * @throws InvalidCpf
     */
    public static function from(#[\SensitiveParameter] string $value): self
    {
        if (! CpfValidator::isValid($value)) {
            throw new InvalidCpf;
        }

        return new self(str_replace(['.', '-'], replace: '', subject: trim($value)));
    }

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
}
