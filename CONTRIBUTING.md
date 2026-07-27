# Contributing to the Magna Plugin SDK

This package is the plugin authoring API. It is deliberately small and
**dependency-light** — it must keep installing and static-analysing standalone,
with only `illuminate/contracts` and `composer/semver`. Do not add runtime
dependencies.

## Setup

```bash
git clone https://github.com/Magna-CMS/Magna-Plugin-SDK.git && cd Magna-Plugin-SDK
composer install
```

## Quality gates — all must pass

Enforced in CI ([`.github/workflows/ci.yml`](.github/workflows/ci.yml)):

```bash
vendor/bin/pint --test        # code style (PSR-12)
vendor/bin/phpstan analyse     # static analysis, level 9, zero errors
vendor/bin/phpunit             # tests
composer validate --strict     # composer metadata
```

## Rules

- **Public API is versioned.** Adding a contract, method or optional manifest
  field is a backwards-compatible minor change. Removing or renaming a public
  symbol is a breaking change (major only) — `tests/PublicApiStabilityTest.php`
  will fail if you do it by accident. See the API stability policy in
  [`docs/04-reference/20-api-stability.md`](docs/04-reference/20-api-stability.md).
- **No new runtime dependencies.** If a helper needs a concrete Laravel class not
  in `illuminate/contracts`, it stays out of the SDK.
- Every behaviour change lands with a test. Documentation (canonical Markdown in
  [`docs/`](docs)) is updated when behaviour changes.

Security issues: see [SECURITY.md](SECURITY.md) — never a public issue.
