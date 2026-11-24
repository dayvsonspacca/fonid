<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared;

use Fonid\Domain\Shared\PersonalDataCannotBeSerialized;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\Shared\Fixtures\SamplePersonalData;

final class PersonalDataTest extends TestCase
{
    #[Test]
    public function dumps_show_only_the_masked_value(): void
    {
        $dump = print_r(new SamplePersonalData('secret-42'), return: true);

        self::assertStringContainsString('***42', $dump);
        self::assertStringNotContainsString('secret', $dump);
    }

    #[Test]
    public function cannot_be_serialized(): void
    {
        $this->expectException(PersonalDataCannotBeSerialized::class);

        serialize(new SamplePersonalData('secret-42'));
    }

    #[Test]
    public function cannot_be_unserialized(): void
    {
        $class = SamplePersonalData::class;

        $this->expectException(PersonalDataCannotBeSerialized::class);

        unserialize(sprintf('O:%d:"%s":1:{s:5:"value";s:9:"secret-42";}', strlen($class), $class));
    }

    #[Test]
    public function the_serialization_error_does_not_include_the_value(): void
    {
        try {
            serialize(new SamplePersonalData('secret-42'));
            self::fail('Serialization should have thrown.');
        } catch (PersonalDataCannotBeSerialized $e) {
            self::assertStringNotContainsString('secret', $e->getMessage());
        }
    }
}
