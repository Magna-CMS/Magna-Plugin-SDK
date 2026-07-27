# Performance & scalability

How Magna stays fast as installs grow, and the rules a plugin should follow to
keep it that way. These are enforced mechanisms, not aspirations.

## Enforced in CI

- **No N+1, anywhere.** `Model::preventLazyLoading()` is active across the whole
  test suite — a lazy relation load in any code path throws and fails the build.
- **Query-count budgets.** Hot endpoints assert a constant query count
  regardless of row count. The delivery list/single endpoints and the management
  users list each have a budget test (`group('query-budget')`); a paginated list
  that starts issuing per-row queries fails CI.
- **Benchmark harness.** A reproducible k6 scenario suite + a data seeder
  (`magna:bench:seed`) live in the repo (`benchmarks/`, `docker-compose.benchmark.yml`),
  run by a CI workflow. Latency budgets are measured against a large seeded
  dataset on a fixed reference environment, and a regression beyond threshold
  fails the run. This is how latency claims stay honest — numbers, reproducible,
  CI-checked.

## Delivery caching

The public delivery API is built for cache efficiency:

- **Surrogate keys** on every response (`entry:123`, `type:article`) so
  publishing an entry purges exactly the affected responses — app cache and,
  where configured, the edge.
- **ETags + conditional requests** — a `304` costs no database queries.
- **Tag-based invalidation** via a post-commit listener, so a write never leaves
  stale cache and never over-purges.

## Runtime & scale

- **Stateless app servers.** Horizontal scale is scale-out behind shared
  Redis/DB. FrankenPHP/Octane worker mode is the reference deployment; plain
  PHP-FPM stays supported.
- **Queues for everything off the critical path** — media conversions, webhooks,
  indexing, audit writes. Jobs are dispatched *after* the database commit, so a
  worker never races the transaction.
- **Plugin discovery is memoised** per request; the dependency graph is resolved
  once per boot. Adding plugins does not multiply filesystem scans.
- **Pagination is mandatory** on list endpoints — no unbounded result sets.

## What a plugin should do

- Eager-load relations you render in lists; never lazy-load in a loop.
- Paginate your own list endpoints.
- Push heavy work to a queued job (see [Security & performance](/docs/security-and-performance)).
- Cache expensive, stable reads with a key that invalidates on write.
- Keep `register()`/`boot()` cheap — they run every request.

## Intentional deviations from vanilla Laravel

- **Dynamic content models** (`Entry::type($handle)`) resolve a per-type table.
  This is deliberate for a multi-content-type CMS; the query-budget tests prove
  it stays efficient.
- **Delivery uses a transformer + response cache**, not API Resources, because
  the transformer is the surrogate-key/ETag seam. Management (write) API uses
  API Resources. Both are intentional.

## Related

- [Security & performance](/docs/security-and-performance)
- [Developer tooling](/docs/developer-tooling) — `magna:plugin:doctor`
