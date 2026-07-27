<?php

declare(strict_types=1);

namespace Magna\Plugins;

use Composer\Semver\VersionParser;
use Magna\Plugins\Exceptions\InvalidManifestException;

final class ManifestValidator
{
    /** vendor/package: lowercase, hyphens/underscores allowed in each segment. */
    private const NAME_PATTERN = '/^[a-z0-9]([a-z0-9_-]*[a-z0-9])?\/[a-z0-9]([a-z0-9_-]*[a-z0-9])?$/';

    private const REQUIRED_FIELDS = ['name', 'displayName', 'description', 'version', 'author', 'license', 'compat', 'entry', 'permissions'];

    /**
     * S1-07: the package segment of a plugin's "vendor/package" name becomes
     * its route-prefix slug (PluginManager::loadRoutes() registers plugin
     * routes under api/v1/{slug}). Because core kernel modules boot before
     * PluginsServiceProvider (see MagnaServiceProvider::register()), a
     * plugin using one of these slugs can register a route at the exact
     * same literal path as a core route (e.g. a plugin named "x/auth"
     * shipping "POST /login" lands on "api/v1/auth/login", the same path
     * Magna's own login endpoint would use) — Laravel's route table is a
     * plain overwritable array keyed by method+URI, so the later
     * registration silently wins on every request. Reserving these slugs
     * at manifest-validation time closes that off entirely, rather than
     * relying on the current (undocumented, refactor-fragile) provider
     * registration order in MagnaServiceProvider to keep them apart.
     *
     * Deliberately excludes "marketplace": the core `src/Magna/Marketplace`
     * library registers no routes of its own (it's consumed internally by
     * the separately-installed magna-cms/marketplace plugin), so there is
     * no actual collision surface, and reserving it would break that real
     * production plugin's own package name.
     *
     * @var list<string>
     */
    private const RESERVED_SLUGS = [
        'admin', 'audit', 'auth', 'blocks', 'content', 'delivery', 'install',
        'management', 'media', 'plugins', 'privacy', 'settings', 'users', 'webhooks',
    ];

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidManifestException
     */
    public static function validate(array $data): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidManifestException("Missing required field in magna.json: {$field}");
            }
        }

        $name = $data['name'];
        if (! is_string($name) || ! preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidManifestException('The "name" field must be in vendor/package format (lowercase, hyphens/underscores allowed).');
        }

        $slug = str_contains($name, '/') ? substr($name, strrpos($name, '/') + 1) : $name;
        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            throw new InvalidManifestException(
                "The package name \"{$name}\" uses the reserved slug \"{$slug}\", which collides with a core Magna module's route prefix. Choose a different package name.",
            );
        }

        $compat = $data['compat'];
        if (! is_array($compat)) {
            throw new InvalidManifestException('The "compat" field must be an object.');
        }

        if (! isset($compat['magna']) || ! is_string($compat['magna'])) {
            throw new InvalidManifestException('The "compat.magna" field is required and must be a string semver constraint.');
        }

        $permissions = $data['permissions'];
        if (! is_array($permissions)) {
            throw new InvalidManifestException('The "permissions" field must be an array.');
        }

        foreach ($permissions as $key => $perm) {
            if (! is_string($perm)) {
                throw new InvalidManifestException("Permission at index {$key} must be a string.");
            }
            if (! preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)+$/', $perm)) {
                throw new InvalidManifestException("Permission \"{$perm}\" must be a dot-separated key (e.g. \"plugin.resource.action\").");
            }
        }

        if (! is_string($data['entry'])) {
            throw new InvalidManifestException('The "entry" field must be a fully-qualified class name string.');
        }

        $version = $data['version'];
        if (! is_string($version) || ! preg_match('/^\d+\.\d+\.\d+/', $version)) {
            throw new InvalidManifestException('The "version" field must be a semver version string (e.g. "1.2.0").');
        }

        if (isset($data['uninstall'])) {
            $uninstall = $data['uninstall'];
            if (! is_array($uninstall)) {
                throw new InvalidManifestException('The "uninstall" field must be an object when present.');
            }
        }

        self::validateDependencyMap($data, 'requires');
        self::validateDependencyMap($data, 'conflict');

        if (isset($data['icon']) && $data['icon'] !== '') {
            $icon = $data['icon'];
            if (! is_string($icon)) {
                throw new InvalidManifestException('The "icon" field must be a string when present.');
            }

            // Must be a plain relative path inside the plugin's own directory —
            // no traversal, no absolute paths, no scheme — since PluginIconController
            // resolves this path directly off the plugin's base_path to serve it.
            if (
                str_contains($icon, '..')
                || str_starts_with($icon, '/')
                || preg_match('#^[a-z][a-z0-9+.-]*://#i', $icon) === 1
                || ! preg_match('/^[a-zA-Z0-9_\-\/.]+\.(svg|png|jpe?g|webp)$/', $icon)
            ) {
                throw new InvalidManifestException('The "icon" field must be a relative path (no "..", no leading "/", no URL) to an svg/png/jpg/webp file within the plugin.');
            }
        }
    }

    /**
     * Validate a dependency map field ("requires" / "conflict"): an object of
     * plugin-name => semver-constraint. Optional; when present every key must be
     * a valid vendor/package name (and not the plugin itself) and every value a
     * parseable semver constraint.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidManifestException
     */
    private static function validateDependencyMap(array $data, string $field): void
    {
        if (! array_key_exists($field, $data)) {
            return;
        }

        $map = $data[$field];
        if (! is_array($map)) {
            throw new InvalidManifestException("The \"{$field}\" field must be an object mapping plugin names to version constraints.");
        }

        $self = is_string($data['name'] ?? null) ? $data['name'] : null;
        $parser = new VersionParser;

        foreach ($map as $name => $constraint) {
            if (! is_string($name) || ! preg_match(self::NAME_PATTERN, $name)) {
                $shown = is_string($name) ? $name : gettype($name);
                throw new InvalidManifestException("The \"{$field}\" field contains an invalid plugin name \"{$shown}\"; expected vendor/package format.");
            }

            if ($name === $self) {
                throw new InvalidManifestException("A plugin cannot list itself (\"{$name}\") in \"{$field}\".");
            }

            if (! is_string($constraint) || $constraint === '') {
                throw new InvalidManifestException("The version constraint for \"{$name}\" in \"{$field}\" must be a non-empty string (e.g. \"^1.0\").");
            }

            try {
                $parser->parseConstraints($constraint);
            } catch (\UnexpectedValueException) {
                throw new InvalidManifestException("The constraint \"{$constraint}\" for \"{$name}\" in \"{$field}\" is not a valid semver constraint.");
            }
        }
    }
}
