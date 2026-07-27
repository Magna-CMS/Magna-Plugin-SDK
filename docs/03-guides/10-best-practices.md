# Best practices

Patterns that keep a Magna plugin maintainable, upgrade-safe and a good citizen
of a site running many plugins. Most are ordinary Laravel discipline.

## Do

- **Keep the entry class thin.** `register()` binds services; `boot()` wires
  routes/views/listeners. Put real logic in your own service classes and inject
  them — don't grow the `Plugin` subclass into a god object.
- **Depend on contracts, not core internals.** Type against the SDK's
  [capability contracts](/docs/capabilities), the manifest, and Laravel
  contracts. Never import `Magna\...` core classes that aren't part of the SDK —
  they aren't covered by the [stability policy](/docs/api-stability).
- **Namespace everything you register.** Prefix views (`blog::`), config keys
  (`blog.*`), permissions (`blog.posts.view`), routes (`/api/v1/blog/...`) and
  table names (`blog_posts`) with your package slug so two plugins never collide.
- **Declare what you need.** Use `requires`/`conflict` in `magna.json` for hard
  [dependencies](/docs/plugin-dependencies); let Magna order and validate them.
- **Make `disable()` reversible.** Tear down runtime state only — never destroy
  user data on disable. Data removal belongs behind `uninstall --purge`.
- **Ship migrations, not manual SQL.** Everything schema-related goes in
  `database/migrations/`; Magna runs them on enable.
- **Test against the real harness.** Extend `PluginTestCase` and assert boot,
  routes and permissions — see [Testing](/docs/plugin-testing).

## Don't (anti-patterns)

- **Don't edit core.** If you find yourself wanting to, you need a capability
  contract or an event — open an issue rather than patching core.
- **Don't reach into other plugins' internals.** For an optional integration,
  check at runtime whether the other plugin is enabled and use its public API;
  never import its private classes.
- **Don't use `app()`/service location in domain classes.** Constructor-inject
  dependencies. The only sanctioned container access is inside the `Plugin`
  lifecycle hooks.
- **Don't put business logic in migrations, routes files or the entry class.**
- **Don't hard-code IDs, URLs or table prefixes** that assume a particular
  install.
- **Don't swallow exceptions silently.** Let Magna's boot isolation catch and
  log genuine failures; don't wrap everything in empty `catch`.

## Related

- [Plugin lifecycle](/docs/plugin-lifecycle)
- [Capabilities](/docs/capabilities)
- [Security & performance](/docs/security-and-performance)

Next: [Security & performance](/docs/security-and-performance).
