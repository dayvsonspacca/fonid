<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Brazil\ValueObjects\Identifiers;

use Fonid\Domain\Brazil\Exceptions\InvalidCpf;
use Fonid\Domain\Brazil\ValueObjects\Identifiers\Cpf;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CpfTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validCpfs(): iterable
    {
        yield 'digits only' => ['52998224725', '52998224725'];
        yield 'masked' => ['529.982.247-25', '52998224725'];
        yield 'surrounding whitespace' => ["  529.982.247-25\n", '52998224725'];
        yield 'first check digit computes to zero' => ['98765432100', '98765432100'];
        yield 'leading zeros' => ['000.000.001-91', '00000000191'];
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
    public function accepts_a_valid_cpf(string $input, string $digits): void
    {
        self::assertSame($digits, Cpf::from($input)->value());
    }

    #[Test]
    #[DataProvider('invalidCpfs')]
    public function rejects_an_invalid_cpf(string $input): void
    {
        $this->expectException(InvalidCpf::class);

        Cpf::from($input);
    }

    #[Test]
    public function formats_with_the_mask(): void
    {
        self::assertSame('529.982.247-25', Cpf::from('52998224725')->formatted());
    }

    #[Test]
    public function equals_the_same_cpf_in_another_format(): void
    {
        self::assertTrue(Cpf::from('52998224725')->equals(Cpf::from('529.982.247-25')));
    }

    #[Test]
    public function does_not_equal_a_different_cpf(): void
    {
        self::assertFalse(Cpf::from('52998224725')->equals(Cpf::from('11144477735')));
    }
}
