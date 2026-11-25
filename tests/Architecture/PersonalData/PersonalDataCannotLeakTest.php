<?php

declare(strict_types=1);

namespace Tests\Architecture\PersonalData;

use Fonid\Analysis\PersonalData\PersonalDataMustNotLeakRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Architecture\PersonalData\Fixtures\ConvertsToJson;
use Tests\Architecture\PersonalData\Fixtures\ConvertsToString;
use Tests\Architecture\PersonalData\Fixtures\ExposesProperty;

/**
 * @extends RuleTestCase<PersonalDataMustNotLeakRule>
 */
final class PersonalDataCannotLeakTest extends RuleTestCase
{
    #[Test]
    public function cannot_be_converted_to_string(): void
    {
        $this->analyse([self::fixture('ConvertsToString')], [
            [ConvertsToString::class.' holds personal data and can be converted to string.', 9],
        ]);
    }

    #[Test]
    public function cannot_be_encoded_to_json(): void
    {
        $this->analyse([self::fixture('ConvertsToJson')], [
            [ConvertsToJson::class.' holds personal data and can be encoded to JSON.', 9],
        ]);
    }

    #[Test]
    public function cannot_expose_a_public_property(): void
    {
        $this->analyse([self::fixture('ExposesProperty')], [
            [ExposesProperty::class.' holds personal data and exposes the public property $value.', 9],
        ]);
    }

    #[Test]
    public function may_expose_its_value_through_explicit_methods(): void
    {
        $this->analyse([self::fixture('ExplicitMethodsOnly')], []);
    }

    #[Test]
    public function does_not_apply_to_other_classes(): void
    {
        $this->analyse([self::fixture('NotPersonalData')], []);
    }

    protected function getRule(): Rule
    {
        return new PersonalDataMustNotLeakRule;
    }

    private static function fixture(string $class): string
    {
        return __DIR__."/Fixtures/{$class}.php";
    }
}
