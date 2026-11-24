# Allow personal data out only through explicit calls

## Status

Accepted

## Context

Fonid handles personal identifiers (CPF now; CNH, CNPJ and other countries' identifiers later) and phone numbers, and the link between them is exactly what it must protect. Data protection laws treat both as personal data in every country Fonid may serve (LGPD in Brazil; the GDPR singles out national identification numbers).

Leaks are rarely deliberate. They happen when a value object is interpolated into a log line or exception message, encoded into a JSON response or log context, dumped on an error page, serialized into a cache or queue (`serialize()` includes private properties), or passed as a string argument that shows up in a stack trace. A rule kept only by convention erodes, and an agent may add a `__toString` for convenience.

This applies to phone numbers as much as to identifiers: a phone number is not an identifier, but it is personal data.

## Decision

No personal data leaves the domain implicitly. The value only comes out through an explicit method call.

- Every value object holding personal data, in any country, extends the abstract `Fonid\Domain\Shared\PersonalData`.
- Values come out only through explicit methods (`value()`, `formatted()`). Every subclass implements `masked()`, the form for logs.
- No string or JSON conversion: no `__toString` (so no `Stringable`) and no `JsonSerializable`. The base deliberately defines none of them. Without them, PHP and PHPStan reject casts and interpolation before the code runs, and `json_encode()` of private properties yields `{}`.
- The base makes `__serialize()` and `__unserialize()` final and throwing, because no static check covers serialization. Code that queues or caches personal data passes the explicit value.
- The base makes `__debugInfo()` final and returns the masked form, so dumps and error pages keep working.
- Properties are private. Factories mark the raw value `#[\SensitiveParameter]`. Exceptions never include the value.

Enforcement:

- A PHPStan rule, `Tests\PHPStan\PersonalDataMustNotLeakRule`, fails any subclass of `PersonalData` that is `Stringable`, `JsonSerializable` or has public properties, wherever it lives.
- A Mago guard rule requires every class under `Fonid\Domain\*\ValueObjects\Identifiers\**` to extend `PersonalData`, catching an identifier that forgot the base. `mago guard` runs in `composer check`.

This prevents accidental output, not deliberate access: reflection, `(array)` casts and `var_export()` still read private properties.

Alternatives considered:

- **A base whose `__toString`, `jsonSerialize` and `__debugInfo` throw.** It makes the class `Stringable`, moving the error from analysis to runtime; it can throw inside error handling and lose the original error; and it breaks debugging.
- **The Mago guard alone, with an `only-public-methods` allowlist** (tested on Mago 1.61). It selects classes by folder only, can't forbid public properties, and the allowlist must grow with every public method. `must-implement = "@nothing"` doesn't see the implicit `Stringable` and forbids every interface.
- **The PHPStan rule alone.** It can't see a class that doesn't extend the base.
- **Protecting identifiers only.** Phone numbers are personal data too.

## Consequences

- Accidental output through logs, messages, JSON, dumps, caches and queues fails at analysis or at runtime instead of leaking silently.
- Every read of a value is an explicit, searchable call.
- Queuing or caching personal data takes an explicit step.
- CNPJ is public data in Brazil but follows the rule anyway, to keep one rule without exceptions.
- A personal data class outside `Identifiers` that doesn't extend the base is caught by neither tool; review has to catch it.
- The PHPStan rule uses PHPStan's internal API and may need changes on major upgrades.
- Two tools carry the rule instead of one.
