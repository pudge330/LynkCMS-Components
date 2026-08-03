# AGENTS.md

## Project overview

This repository provides the reusable `lynkcms/components` PHP library. It is critical to LynkCMS, but it must remain usable outside LynkCMS by other PHP applications that need only some of its functionality.

The current Composer baseline is PHP 7.0 with legacy development dependencies. The long-term goal is PHP 8 and current supported dependency versions. Treat that work as an intentional, incremental migration:

- Keep new code compatible with the currently declared PHP constraint unless the task explicitly advances the PHP migration boundary.
- When raising the PHP baseline, update code, Composer constraints, dependencies, tests, and documentation together.
- Prefer supported stable dependency releases and documented upgrade paths. Review changelogs and migration guides for major-version upgrades.
- Keep migration steps focused and reviewable. Do not introduce PHP 8-only syntax incidentally while the package still declares PHP 7 support.
- Do not claim PHP 8 compatibility based only on syntax checks; identify the runtime versions actually exercised.

## Library boundaries

- Keep components independent of the LynkCMS application and `lynkcms/core`. Do not introduce site configuration, CMS bootstrap assumptions, global application state, or dependencies on project-specific paths.
- Depend on narrow contracts and explicit inputs. Prefer constructor or method injection over service locators and hidden globals.
- A component should be usable independently when practical. Avoid making consumers install unrelated frameworks or packages for functionality they do not use.
- Keep required dependencies minimal. Make integrations optional when they are not fundamental to the library, and fail clearly when an optional integration is unavailable.
- Preserve public APIs unless the task explicitly authorizes a breaking change. For deliberate breaking changes, update affected tests and documentation and describe the migration path.
- Do not copy application-specific behavior into this library merely for convenience. Generalize only when there is a concrete reusable requirement.
- Avoid adding compatibility shims without a defined consumer and removal plan.

## Repository map

- `src/`: component source code under the `Lynk\Component\` namespace.
- `src/Functions/`: legacy function autoloading and the lowercase `lynk\` namespace; treat changes here carefully because functions are global public API.
- `tests/`: PHPUnit tests.
- `composer.json`: package metadata, autoloading, PHP support, and dependency constraints.
- `phpunit.xml`: test-suite configuration.

## Working rules

- Inspect nearby code before editing and follow its established conventions unless a migration task intentionally changes them.
- Follow applicable current PHP-FIG and PSR recommendations where practical. When legacy runtime support prevents strict compliance, choose the simplest safe approach and explain the exception.
- Prefer straightforward, readable code over elaborate abstractions. Developers with varied experience levels must be able to maintain this library.
- Use clear names, predictable control flow, small focused units, and comments only for non-obvious intent or compatibility constraints.
- Do not add dependencies, layers, design patterns, configuration, or extension points without a concrete current requirement.
- Favor standard PHP and small interfaces over framework-specific types when doing so preserves useful interoperability.
- Validate external input at the appropriate boundary and avoid exposing secrets or sensitive values in logs and exceptions.
- Do not edit generated files, Composer `vendor/` contents, test caches, or build artifacts.
- Do not stage, commit, or push changes unless explicitly requested.

## Dependency and PHP upgrades

- Run Composer validation and inspect dependency resolution before changing a lockfile or declaring an upgrade complete.
- Update direct dependencies deliberately; do not bundle unrelated major upgrades into one change.
- Check the lowest and highest supported PHP versions when those runtimes are available.
- Account for deprecations before removing the older runtime from the supported range.
- Keep `composer.json`, tests, CI or tooling configuration, and documented requirements consistent.
- Prefer the latest compatible supported version, not merely the numerically newest release, when consumer or runtime constraints still apply.

## Validation

Validate proportionally to the change:

1. Run `php -l` on every changed PHP file.
2. Run `composer validate` after Composer metadata changes.
3. Run the relevant PHPUnit tests, followed by the full suite when feasible.
4. For autoloading or public API changes, exercise the package through Composer's generated autoloader.
5. For dependency or PHP migrations, report the PHP versions and dependency set actually tested, plus any unavailable matrix coverage.

Before handing off, review `git diff` and `git status`. Preserve unrelated user changes and report checks that could not be completed.
