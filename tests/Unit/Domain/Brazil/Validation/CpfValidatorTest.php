<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Brazil\Validation;

use Fonid\Domain\Brazil\Validation\CpfValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CpfValidatorTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function validCpfs(): iterable
    {
        yield 'digits only' => ['52998224725'];
        yield 'masked' => ['529.982.247-25'];
        yield 'surrounding whitespace' => ["  529.982.247-25\n"];
        yield 'first check digit computes to zero' => ['98765432100'];
        yield 'leading zeros' => ['000.000.001-91'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCpfs(): iterable
    {
        yield 'empty' => [''];
        yield 'wrong first check digit' => ['52998224735'];
        yield 'wrong second check digit' => ['52998224726'];
        yield 'all digits equal' => ['111.111.111-11'];
        yield 'all zeros' => ['00000000000'];
        yield 'too short' => ['5299822472'];
        yield 'too long' => ['529982247250'];
        yield 'letters' => ['5299822472a'];
        yield 'partial mask' => ['529.98224725'];
        yield 'mask with wrong separators' => ['529-982-247.25'];
        yield 'spaces between groups' => ['529 982 247 25'];
    }

    #[Test]
    #[DataProvider('validCpfs')]
    public function accepts_a_valid_cpf(string $input): void
    {
        self::assertTrue(CpfValidator::isValid($input));
    }

    #[Test]
    #[DataProvider('invalidCpfs')]
    public function rejects_an_invalid_cpf(string $input): void
    {
        self::assertFalse(CpfValidator::isValid($input));
    }
}
