<?php

declare(strict_types=1);

namespace Magna\Contracts;

use Illuminate\Http\Request;

/**
 * A pre-authentication gate the login seam runs before credentials are checked.
 * Semver-guaranteed from core 1.0.
 *
 * Framework-light on purpose: a check names a Blade *view* to render on the
 * form (core, which owns the admin stack, maps it into the Livewire login);
 * the SDK never references Filament or Livewire types.
 *
 * Contract the seam guarantees:
 *  - assert() denies by throwing Illuminate\Validation\ValidationException and
 *    continues by returning. It returns no value and must not mutate auth state
 *    — there is no approval path a check controls; the real login still runs
 *    afterwards. This makes "accidentally turn denial into approval" impossible.
 *  - On any *other* throwable (a bug), the seam applies failMode() (see FailMode).
 */
interface LoginCheck
{
    /** The surface this check guards, e.g. 'magna.admin.login'. */
    public function surface(): string;

    /** Blade view to render on the login form, or null if the check is invisible. */
    public function view(): ?string;

    /** How the seam treats an unexpected throwable from assert(). */
    public function failMode(): FailMode;

    /**
     * Assert the attempt may proceed. Throw ValidationException to deny.
     *
     * @param  array<string, mixed>  $state  the submitted form state
     */
    public function assert(array $state, Request $request): void;
}
