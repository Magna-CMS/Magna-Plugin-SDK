# Security & performance

A plugin runs with full application access. Treat it like core code.

## Security

- **Authorize every action.** Gate admin and API actions behind the permissions
  you declare in `magna.json`. Register a permission before you check it; never
  assume the caller is allowed.
- **Validate all input.** Use Form Requests or `$request->validate()` for every
  write endpoint. Never trust request data, route parameters or manifest values
  as safe.
- **Never trust file paths from input.** If a plugin serves files, whitelist
  them — no user value should reach the filesystem (path traversal). Magna's own
  asset endpoint uses a strict filename allowlist; follow the same pattern.
- **Respect the security boundary.** Do not re-alias core middleware, rebind
  security singletons, or `Event::forget()` core listeners in `boot()` — Magna
  snapshots security-critical state before booting plugins and reverts tampering,
  logging it as critical. Work with the extension points, not around them.
- **Keep secrets out of the manifest and VCS.** Configuration secrets belong in
  the admin Settings layer or `.env`, never in `magna.json` or committed config.
- **Scope your data to the user** when handling personal data, and implement
  [`HandlesPersonalData`](/docs/capabilities) so GDPR export/erase stays complete.

## Performance

- **Do heavy work in queued jobs**, not in-request. Dispatch to the queue for
  thumbnails, webhooks, imports — never block a page load.
- **Avoid N+1 queries.** Eager-load relations you render in lists. Magna's own
  API resources eager-load before serialising; do the same.
- **Cache expensive, stable reads** — but only where correctness is guaranteed,
  and key the cache so it invalidates when the underlying data changes.
- **Keep `register()`/`boot()` cheap.** They run on every request. No DB queries
  or filesystem scans in the hot path; defer to lazy resolution.
- **Don't scan the filesystem at runtime.** Declare assets/paths; resolve once.
- **Paginate list endpoints.** Never return unbounded collections.

## Related

- [Best practices](/docs/best-practices)
- [Plugin lifecycle](/docs/plugin-lifecycle) — boot isolation & the security snapshot
- [Capabilities](/docs/capabilities) — `HandlesPersonalData`

Next: [Troubleshooting](/docs/troubleshooting).
