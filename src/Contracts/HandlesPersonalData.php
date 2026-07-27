<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Plugin contract: GDPR personal-data lifecycle hooks.
 * Semver-guaranteed from core 1.0.
 *
 * Any plugin that stores personal data tied to a user MUST implement this
 * contract. The core magna:privacy:export and magna:privacy:erase commands
 * iterate all enabled plugins and invoke these methods. A plugin that stores
 * personal data but does not implement this contract will cause magna:privacy:erase
 * to report an incomplete erasure (data may remain in the plugin's tables).
 *
 * The user is typed as Laravel's Authenticatable contract (not the concrete
 * core User model) so this SDK contract carries no dependency on core — key
 * your plugin's data by `$user->getAuthIdentifier()`.
 */
interface HandlesPersonalData
{
    /**
     * Return a serialisable snapshot of all personal data this plugin holds for
     * the given user. The return value is merged into the export JSON archive
     * under a key matching the plugin's name.
     *
     * @return array<string, mixed>
     */
    public function exportPersonalData(Authenticatable $user): array;

    /**
     * Permanently delete or anonymise all personal data this plugin holds for
     * the given user. Called by magna:privacy:erase after the core user record
     * has been anonymised.
     */
    public function erasePersonalData(Authenticatable $user): void;
}
