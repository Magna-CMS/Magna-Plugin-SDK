# Publishing & releasing

How to version, release and maintain a plugin so its users can upgrade safely.

## Versioning

Follow [Semantic Versioning](https://semver.org) for your plugin's `version`:

- **PATCH** — bug fixes, no API change.
- **MINOR** — backwards-compatible additions.
- **MAJOR** — backwards-incompatible changes.

Declare the core you support with `compat.magna` (e.g. `^1.0`). Magna refuses to
enable a plugin whose `compat.magna` doesn't match the running core.

## Pre-release checklist

Run these before tagging a release — they are the same gates Magna's own SDK CI
enforces:

```bash
vendor/bin/pint --test          # code style
vendor/bin/phpstan analyse       # static analysis
vendor/bin/phpunit               # tests
php artisan magna:plugin:validate acme/blog --strict   # manifest + graph
```

Bump `version` in `magna.json` (and `composer.json`), update your changelog, tag
the release.

## Continuous integration

Wire the same checks into CI so every push is validated. A minimal GitHub
Actions job:

```yaml
name: CI
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '8.4' }
      - run: composer install --no-interaction --prefer-dist
      - run: vendor/bin/pint --test
      - run: vendor/bin/phpstan analyse --no-progress
      - run: vendor/bin/phpunit
```

## Distributing

A plugin is an ordinary Composer package. Publish it to Packagist (or a private
Composer repository) and users install it with `composer require vendor/package`,
then enable it with `php artisan magna:plugin:enable vendor/package`.

## Releasing a new major version

Breaking changes ship only in a MAJOR release. Give users a path:

- Deprecate first: mark the old API `@deprecated` in a MINOR, naming the
  replacement. Keep it working for the rest of that major.
- Document every breaking change and its migration step in an `UPGRADING.md`.
- Remove deprecated APIs only in the next MAJOR.

This mirrors the SDK's own [API stability policy](/docs/api-stability) — hold
your plugins to the same contract your users hold the SDK to.

## Upgrading a plugin (for consumers)

- MINOR/PATCH: `composer update`, no code changes expected.
- MAJOR: read the plugin's `UPGRADING.md`, apply the steps, then run
  `php artisan magna:plugin:validate --strict` to confirm the manifest and
  dependency graph still hold.

## Related

- [API stability](/docs/api-stability)
- [Developer tooling](/docs/developer-tooling)
- [Plugin dependencies](/docs/plugin-dependencies)
