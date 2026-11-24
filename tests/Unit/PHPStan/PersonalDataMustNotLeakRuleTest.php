<?php

declare(strict_types=1);

namespace Tests\Unit\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\PHPStan\PersonalDataMustNotLeakRule;

/**
 * @extends RuleTestCase<PersonalDataMustNotLeakRule>
 */
final class PersonalDataMustNotLeakRuleTest extends RuleTestCase
{
    #[Test]
    public function reports_personal_data_that_can_leave_implicitly(): void
    {
        $files = array_map(
            static fn (string $class): string => __DIR__."/data/{$class}.php",
            ['Clean', 'ConvertsToString', 'ConvertsToJson', 'ExposesProperty', 'NotPersonalData'],
        );

        $this->analyse($files, [
            ['Tests\Unit\PHPStan\Data\ConvertsToString holds personal data and can be converted to string.', 9],
            ['Tests\Unit\PHPStan\Data\ConvertsToJson holds personal data and can be encoded to JSON.', 9],
            ['Tests\Unit\PHPStan\Data\ExposesProperty holds personal data and exposes the public property $value.', 9],
        ]);
    }

    protected function getRule(): Rule
    {
        return new PersonalDataMustNotLeakRule;
    }
}
