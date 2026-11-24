<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Brazil\ValueObjects\Identifiers;

use Fonid\Domain\Brazil\Exceptions\InvalidCpf;
use Fonid\Domain\Brazil\ValueObjects\Identifiers\Cpf;
use Fonid\Domain\Shared\PersonalData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SensitiveParameterValue;

final class CpfPrivacyTest extends TestCase
{
    #[Test]
    public function is_personal_data(): void
    {
        self::assertInstanceOf(PersonalData::class, Cpf::from('52998224725'));
    }

    #[Test]
    public function masks_all_but_the_middle_digits(): void
    {
        self::assertSame('***.982.247-**', Cpf::from('52998224725')->masked());
    }

    #[Test]
    public function the_error_does_not_include_the_input(): void
    {
        try {
            Cpf::from('52998224726');
            self::fail('An invalid CPF should have thrown.');
        } catch (InvalidCpf $e) {
            self::assertStringNotContainsString('52998224726', $e->getMessage());
        }
    }

    #[Test]
    public function the_input_is_redacted_from_stack_traces(): void
    {
        try {
            Cpf::from('52998224726');
            self::fail('An invalid CPF should have thrown.');
        } catch (InvalidCpf $e) {
            $frame = array_find($e->getTrace(), static fn (array $frame): bool => $frame['function'] === 'from');

            self::assertNotNull($frame);
            self::assertInstanceOf(SensitiveParameterValue::class, $frame['args'][0] ?? null);
        }
    }
}
