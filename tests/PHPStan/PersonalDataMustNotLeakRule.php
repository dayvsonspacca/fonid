<?php

declare(strict_types=1);

namespace Tests\PHPStan;

use Fonid\Domain\Shared\PersonalData;
use JsonSerializable;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionProperty;
use Stringable;

/**
 * Fails any subclass of PersonalData that offers an implicit way out:
 * string conversion, JSON encoding or a public property.
 *
 * @see docs/adr/202611241754-allow-personal-data-out-only-through-explicit-calls.md
 *
 * @implements Rule<InClassNode>
 */
final class PersonalDataMustNotLeakRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();

        if ($class->getName() === PersonalData::class || ! $class->is(PersonalData::class)) {
            return [];
        }

        $leaks = [];

        if ($class->hasNativeMethod('__toString') || $class->implementsInterface(Stringable::class)) {
            $leaks[] = 'can be converted to string';
        }

        if ($class->implementsInterface(JsonSerializable::class)) {
            $leaks[] = 'can be encoded to JSON';
        }

        foreach ($class->getNativeReflection()->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $leaks[] = "exposes the public property \${$property->getName()}";
        }

        return array_map(
            static fn (string $leak): IdentifierRuleError => RuleErrorBuilder::message(
                "{$class->getName()} holds personal data and {$leak}.",
            )
                ->identifier('fonid.personalDataLeak')
                ->build(),
            $leaks,
        );
    }
}
