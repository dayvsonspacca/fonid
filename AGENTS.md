# AGENTS.md

Laravel application on PHP 8.6, served by `serversideup/php:8.6-fpm-nginx`.

## Running things

Everything runs in Docker through [`just`](https://just.systems). Don't use the host's `php` or `composer`: the host PHP may be a different version than the project's.

- `just` lists every recipe
- `just artisan <args>`, `just composer <args>`, `just test [--filter=...]`, `just shell`
- `just up` / `just down` serve the app at http://localhost:8000 (`APP_PORT` in `.env`)

## Structure

`app/` (namespace `Fonid\`) is split into layers:

- `Domain/`: the business domain. No dependency on HTTP or the outside layers. Fonid assumes the data exists and may be used; where it comes from is not the domain's concern. The domain declares what data it needs as contracts, and the source is plugged in from outside. Each country has its own folder (e.g. `Domain/Brazil/`); `Domain/Shared/` holds what applies to every country.
- `Interface/`: the output layer, how the domain is exposed. Each country has its own folder under `Interface/Http/Api/`, versioned independently (e.g. `Interface/Http/Api/Brazil/V1/` serves `/api/br/v1/`). See `202611241726-organize-the-domain-and-the-api-by-country`.
- `Providers/`: Laravel service providers (framework wiring).

## Before finishing a task

Run `just check`. It runs Mago format check, Mago lint, Mago guard, PHPStan and PHPUnit, the same as CI, and it must pass. `just fmt` applies Mago's auto-fixes and formats.

## Conventions

- Every PHP file starts with `declare(strict_types=1);`.
- Don't hand-format; run `just fmt`.
- Classes are `final` unless they're designed for extension.
- PHPStan runs at level max (Larastan). Fix the types instead of lowering the level or adding ignores and baseline entries; ask first if there's truly no way around it.
- Value objects holding personal data (identifiers, phone numbers) extend `Fonid\Domain\Shared\PersonalData` and never convert to string or JSON. See `202611241754-allow-personal-data-out-only-through-explicit-calls`.
- Tests are PHPUnit classes with `#[Test]` attributes, in `tests/Unit` and `tests/Feature`.
- Generate classes with `just artisan make:*`. `stubs/` customizes its output to follow these conventions; keep it that way when editing a stub.
- `config/` holds only the keys that differ from the framework defaults (Laravel merges them over `vendor/laravel/framework/config/`). To change config, add a file with just those keys rather than publishing the full file.
- There is no frontend build (no Vite or npm). Don't add one unless asked.
- Sessions and cache use the `file` driver and the queue is `sync`. Database drivers need their tables first (`just artisan make:session-table`, `make:cache-table`, `make:queue-table`).
- Extra PHP extensions go in the `base` stage of `Dockerfile` (`install-php-extensions`), followed by `just build`.

## Architecture Decision Records

Architectural decisions are recorded in `docs/adr/`, indexed in `docs/adr/README.md`.

- Before an architectural change (new dependency, data model, module boundaries, infrastructure, cross-cutting patterns), read the index and the relevant ADRs.
- Follow Accepted ADRs. If a requested change conflicts with one, say so and stop for confirmation instead of silently breaking it.
- When your work makes a decision future developers will need the "why" for, or one that is architecturally significant, cross-team, or hard to reverse, write an ADR:
  - copy `docs/adr/template.md` to `docs/adr/$(date +%Y%m%d%H%M)-<decision-slug>.md`, naming the decision, not the topic;
  - status Proposed unless the user has agreed to it;
  - describe the real context and the alternatives considered, not generic pros and cons;
  - add it to the index.
- Don't write ADRs for tiny, temporary, or already-documented decisions.
- Never edit an existing ADR except its status. To change a decision, write a new ADR and set the old one's status to `Superseded by [title](./file.md)`.
- After adding an ADR or changing a status, regenerate the index table from the files.

### The ADR check

`scripts/adr.sh` checks the records, the index, the links in every file agents read, and every place code or docs cite a record. CI runs it on every pull request; run it before pushing.

- `scripts/adr.sh new "<decision title>"` creates a record from the template and adds it to the index.
- `scripts/adr.sh --fix` regenerates the index after a status change.
- Cite a record by its file name without `.md` (`YYYYMMDDHHMM-<decision-slug>`), never by a sequence number. The check fails a citation that names no record. It warns on one that names a superseded or deprecated record, since that comment now points at a decision that no longer stands.
- Once a record is merged, the check allows changes to its Status section only, and only forward:
  - Proposed → Accepted or Rejected
  - Accepted → Deprecated or Superseded
  - Deprecated → Superseded
