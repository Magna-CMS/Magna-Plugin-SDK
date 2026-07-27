<?php

declare(strict_types=1);

namespace Magna\Plugins;

use Composer\Semver\Semver;
use Magna\Plugins\Exceptions\DependencyException;

/**
 * Resolves the dependency graph declared by plugin manifests (`requires` /
 * `conflict`). Pure and side-effect-free: it reads manifests and either returns
 * a deterministic boot order or throws a DependencyException describing exactly
 * what failed and how to fix it. Core's PluginManager owns the enable/boot
 * policy; this class owns the graph maths.
 */
final class DependencyResolver
{
    /**
     * Compute a boot order for a set of enabled plugins: every plugin appears
     * after all of its (in-set) dependencies. Validates the whole graph first
     * (missing requirement, unmet version, active conflict, cycle).
     *
     * Ordering is deterministic — independent plugins keep alphabetical order —
     * so the same enabled set always boots identically.
     *
     * @param  array<string, Manifest>  $manifests  Enabled plugins keyed by name.
     * @return list<string> Plugin names in boot order.
     *
     * @throws DependencyException
     */
    public function resolveBootOrder(array $manifests): array
    {
        foreach ($manifests as $manifest) {
            $this->assertRequirements($manifest, $manifests);
            $this->assertNoConflicts($manifest, $manifests);
        }

        $order = [];
        $state = []; // name => 'visiting' | 'visited'

        $names = array_keys($manifests);
        sort($names); // deterministic independent-of-input order

        foreach ($names as $name) {
            $this->visit($name, $manifests, $state, $order, []);
        }

        return $order;
    }

    /**
     * Assert a single candidate plugin can be enabled against the currently
     * enabled set: its requirements are present and version-compatible, and it
     * neither conflicts with, nor is conflicted by, an enabled plugin. Used by
     * the enable() flow to fail early with a targeted message.
     *
     * @param  array<string, Manifest>  $enabled  Currently enabled plugins keyed by name (excluding the candidate).
     *
     * @throws DependencyException
     */
    public function assertCanEnable(Manifest $candidate, array $enabled): void
    {
        $this->assertRequirements($candidate, $enabled);

        // Forward conflict: candidate declares it conflicts with something enabled.
        foreach ($candidate->conflict as $name => $constraint) {
            if (isset($enabled[$name]) && Semver::satisfies($enabled[$name]->version, $constraint)) {
                throw new DependencyException(
                    "Plugin \"{$candidate->name}\" conflicts with \"{$name}\" {$constraint}, which is enabled "
                    ."(version {$enabled[$name]->version}). Disable \"{$name}\" before enabling \"{$candidate->name}\"."
                );
            }
        }

        // Reverse conflict: an enabled plugin declares it conflicts with the candidate.
        foreach ($enabled as $other) {
            foreach ($other->conflict as $name => $constraint) {
                if ($name === $candidate->name && Semver::satisfies($candidate->version, $constraint)) {
                    throw new DependencyException(
                        "Enabled plugin \"{$other->name}\" conflicts with \"{$candidate->name}\" {$constraint}. "
                        ."Disable \"{$other->name}\" before enabling \"{$candidate->name}\"."
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, Manifest>  $available
     *
     * @throws DependencyException
     */
    private function assertRequirements(Manifest $manifest, array $available): void
    {
        foreach ($manifest->requires as $dep => $constraint) {
            if (! isset($available[$dep])) {
                throw new DependencyException(
                    "Plugin \"{$manifest->name}\" requires \"{$dep}\" ({$constraint}), which is not installed or not enabled. "
                    ."Install and enable \"{$dep}\" first."
                );
            }

            if (! Semver::satisfies($available[$dep]->version, $constraint)) {
                throw new DependencyException(
                    "Plugin \"{$manifest->name}\" requires \"{$dep}\" {$constraint}, but version {$available[$dep]->version} is enabled. "
                    ."Update \"{$dep}\" to a version matching {$constraint}."
                );
            }
        }
    }

    /**
     * @param  array<string, Manifest>  $manifests
     *
     * @throws DependencyException
     */
    private function assertNoConflicts(Manifest $manifest, array $manifests): void
    {
        foreach ($manifest->conflict as $name => $constraint) {
            if (isset($manifests[$name]) && Semver::satisfies($manifests[$name]->version, $constraint)) {
                throw new DependencyException(
                    "Plugin \"{$manifest->name}\" conflicts with \"{$name}\" {$constraint}, but \"{$name}\" "
                    ."version {$manifests[$name]->version} is also enabled. Disable one of them."
                );
            }
        }
    }

    /**
     * Depth-first topological visit with cycle detection.
     *
     * @param  array<string, Manifest>  $manifests
     * @param  array<string, string>  $state
     * @param  list<string>  $order
     * @param  list<string>  $path  Current DFS path, for a readable cycle message.
     *
     * @throws DependencyException
     */
    private function visit(string $name, array $manifests, array &$state, array &$order, array $path): void
    {
        if (($state[$name] ?? null) === 'visited') {
            return;
        }

        if (($state[$name] ?? null) === 'visiting') {
            $cycle = array_slice($path, array_search($name, $path, true) ?: 0);
            $cycle[] = $name;
            throw new DependencyException(
                'Circular plugin dependency detected: '.implode(' -> ', $cycle)
                .'. Break the cycle by removing one of these "requires" entries.'
            );
        }

        $state[$name] = 'visiting';
        $path[] = $name;

        // Visit dependencies (that are part of the enabled set) first, in a
        // deterministic order, so the dependency boots before this plugin.
        $deps = array_keys($manifests[$name]->requires);
        sort($deps);
        foreach ($deps as $dep) {
            if (isset($manifests[$dep])) {
                $this->visit($dep, $manifests, $state, $order, $path);
            }
        }

        $state[$name] = 'visited';
        $order[] = $name;
    }
}
