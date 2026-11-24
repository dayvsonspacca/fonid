# Organize the domain and the API by country

## Status

Accepted

## Context

Fonid answers one question: given a phone number and a personal identifier, does the phone belong to that person? Every part of that question depends on the country:

- **Identifiers.** Each country has its own (CPF in Brazil), with its own format, validation rules and meaning. Brazil separates natural persons (CPF) from legal entities (CNPJ); other countries draw that line differently or not at all.
- **Phone numbers.** Each country has its own numbering plan.
- **Source of truth.** Who owns a line is known by national operators and registries, and whether an answer exists at all depends on national rules. Brazil requires prepaid lines to be registered with a CPF (Lei 10.703/2003); in a country without SIM registration there may be no reliable source. Adding a country means finding a source, not flipping a setting.
- **Data protection law.** Linking an identity to a phone number is processing personal data, and even a yes/no answer discloses it. Legal basis, purpose requirements, audit, retention and limits against enumeration (testing many numbers against one identifier) differ per country (LGPD in Brazil).

A single global model would end up as a lowest common denominator or a bag of optional fields. Country rules would be scattered across shared code, where one country's rule can leak into another, or be missing for a new one.

## Decision

The country is the top-level boundary of both the domain and the API.

- **Domain:** each country has its own folder, `app/Domain/<Country>/` (e.g. `Domain/Brazil/`), with its own identifier and phone types, validation and data-source contracts.
- **API:** routes live under `/api/<iso>/v<n>/`, where `<iso>` is the lowercase ISO 3166-1 alpha-2 code (`/api/br/v1/`). The code lives in `app/Interface/Http/Api/<Country>/V<n>/`.
- **Versions are per country.** A breaking change in one country bumps only that country's version.
- **The country in the path defines both inputs.** Under `/api/br/` the identifier is Brazilian and the phone number must be a Brazilian number (+55). Anything else is rejected as invalid input. Cross-country checks are out of scope.
- **Cross-cutting conventions are shared** across countries: error format, authentication, pagination. Only resources and policies vary per country.

Alternatives considered:

- **A global API with the country as a parameter** (`/api/v1/verify?country=br`). It forces one model across countries and turns per-country policy into conditionals instead of a boundary.
- **Splitting by identifier type** (`/api/v1/cpf/...`). Identifier types are already country-specific, and the country is where law and data sources apply. If truly international identifiers (passport, IBAN) ever matter, they can get their own namespace next to the countries.
- **A global version with the country under it** (`/api/v1/br/`). A breaking change in one country would force a new version on every country.

## Consequences

- Each country's rules, data sources and legal policies sit in one place. Adding or changing a country doesn't touch the others.
- Each country can be released, changed and deprecated on its own schedule.
- Clients that integrate several countries call several endpoints, possibly on different versions.
- Countries can drift apart. The shared conventions have to be maintained on purpose to prevent it.
- A new country needs a data source and a legal basis before it can be offered, and may turn out not to be offerable at all.
- A foreign phone number can't be checked against a Brazilian identifier, even when the person really owns it.
- The existing `Interface/Http/Api/V1/Brazil/` folder moves to `Interface/Http/Api/Brazil/V1/`, and `AGENTS.md` is updated to match.
