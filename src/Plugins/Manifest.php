<?php

declare(strict_types=1);

namespace Magna\Plugins;

use Composer\Semver\Semver;
use Magna\Plugins\Exceptions\InvalidManifestException;

/**
 * Immutable value object representing a validated magna.json manifest.
 */
final class Manifest
{
    /**
     * @param  list<string>  $permissions
     * @param  array<string, mixed>  $provides
     * @param  array<mixed, mixed>|null  $uninstall
     * @param  string|null  $icon  Relative path to a square icon (svg/png/jpg/webp) within the plugin's own directory — same convention as a VS Code extension's package.json "icon" field. Optional: with none set, the admin UI falls back to a letter avatar.
     * @param  array<string, string>  $requires  Hard dependencies: other plugin names → semver constraint. Every one must be installed, enabled and version-compatible before this plugin can boot; they boot first.
     * @param  array<string, string>  $conflict  Plugin names → semver constraint this plugin is incompatible with. Enabling both is refused.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $displayName,
        public readonly string $description,
        public readonly string $version,
        public readonly string $author,
        public readonly string $license,
        public readonly string $magnaCompat,
        public readonly string $phpCompat,
        public readonly string $entryClass,
        public readonly array $provides,
        public readonly array $permissions,
        public readonly ?array $uninstall,
        public readonly ?string $icon = null,
        public readonly array $requires = [],
        public readonly array $conflict = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        ManifestValidator::validate($data);

        $compat = $data['compat'];
        $compatMagna = is_array($compat) && is_string($compat['magna'] ?? null) ? (string) $compat['magna'] : '^1.0';
        $compatPhp = is_array($compat) && is_string($compat['php'] ?? null) ? (string) $compat['php'] : '^8.3';

        $permissions = is_array($data['permissions'])
            ? array_values(array_filter($data['permissions'], 'is_string'))
            : [];

        $uninstall = isset($data['uninstall']) && is_array($data['uninstall']) ? $data['uninstall'] : null;
        $provides = isset($data['provides']) && is_array($data['provides']) ? $data['provides'] : [];
        $icon = isset($data['icon']) && is_string($data['icon']) && $data['icon'] !== '' ? $data['icon'] : null;

        return new self(
            name: self::requireString($data, 'name'),
            displayName: self::requireString($data, 'displayName'),
            description: self::requireString($data, 'description'),
            version: self::requireString($data, 'version'),
            author: self::requireString($data, 'author'),
            license: self::requireString($data, 'license'),
            magnaCompat: $compatMagna,
            phpCompat: $compatPhp,
            entryClass: self::requireString($data, 'entry'),
            provides: $provides,
            permissions: $permissions,
            uninstall: $uninstall,
            icon: $icon,
            requires: self::stringMap($data, 'requires'),
            conflict: self::stringMap($data, 'conflict'),
        );
    }

    /**
     * Extract a map of string keys to string values (e.g. plugin name → semver
     * constraint), dropping any entry whose key or value is not a string.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private static function stringMap(array $data, string $key): array
    {
        $raw = $data[$key] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        $map = [];
        foreach ($raw as $name => $constraint) {
            if (is_string($name) && is_string($constraint)) {
                $map[$name] = $constraint;
            }
        }

        return $map;
    }

    public function isCompatibleWith(string $coreVersion): bool
    {
        return Semver::satisfies($coreVersion, $this->magnaCompat);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'version' => $this->version,
            'author' => $this->author,
            'license' => $this->license,
            'compat' => ['magna' => $this->magnaCompat, 'php' => $this->phpCompat],
            'entry' => $this->entryClass,
            'provides' => $this->provides,
            'permissions' => $this->permissions,
            'uninstall' => $this->uninstall,
            'icon' => $this->icon,
            'requires' => $this->requires,
            'conflict' => $this->conflict,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidManifestException
     */
    private static function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (! is_string($value)) {
            throw new InvalidManifestException("Field \"{$key}\" must be a string.");
        }

        return $value;
    }

    /**
     * @throws InvalidManifestException
     */
    public static function loadFromFile(string $path): self
    {
        if (! file_exists($path)) {
            throw new InvalidManifestException("Manifest file not found: {$path}");
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new InvalidManifestException("Cannot read manifest: {$path}");
        }

        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new InvalidManifestException("Manifest is not a JSON object: {$path}");
        }

        /** @var array<string, mixed> $data */
        return self::fromArray($data);
    }
}
