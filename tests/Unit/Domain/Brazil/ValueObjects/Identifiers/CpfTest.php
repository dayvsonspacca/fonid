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
    public static function inputs(): iterable
    {
        yield 'digits only' => ['52998224725', '52998224725'];
        yield 'masked' => ['529.982.247-25', '52998224725'];
        yield 'surrounding whitespace' => ["  529.982.247-25\n", '52998224725'];
        yield 'leading zeros' => ['000.000.001-91', '00000000191'];
    }

    #[Test]
    #[DataProvider('inputs')]
    public function keeps_only_the_digits(string $input, string $digits): void
    {
        self::assertSame($digits, Cpf::from($input)->value());
    }

    #[Test]
    public function rejects_an_invalid_cpf(): void
    {
        $this->expectException(InvalidCpf::class);

        Cpf::from('52998224726');
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
