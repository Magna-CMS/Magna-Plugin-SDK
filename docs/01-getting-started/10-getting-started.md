# Introduction

Magna is an API-first, modular content platform built on Laravel. Everything
beyond the core — blogs, e-commerce, docs sites, dashboards — is a **plugin**, and
plugins are first-class: they add admin screens, content types, routes,
permissions, widgets, webhooks and console commands without ever editing core.

This manual is the official guide to building those plugins. It is written for
Laravel developers: if you know Laravel, you already know most of what you need.

## The Plugin SDK

The **Magna Plugin SDK** (`magna-cms/plugin-sdk`) is the stable, versioned public
API your plugin compiles against — the extension contracts, the `Plugin` base
class, and the `magna.json` manifest specification. It is dependency-light
(`illuminate/contracts` + `composer/semver`) so you can build and test a plugin
in your own repository without pulling in Magna's core internals.

The SDK follows [Semantic Versioning](/docs/api-stability): within a major
version your plugin keeps working, and every public API here is one you can rely
on for the long term.

## How this manual is organised

**Getting Started**
- [Introduction](/docs/getting-started) — you are here
- [Installation](/docs/installation) — install Magna, then set up for plugin development
- [Creating a plugin](/docs/creating-a-plugin) — scaffold and enable your first plugin

**Building Plugins**
- [Plugin manifest](/docs/plugin-manifest) — every `magna.json` field
- [Plugin lifecycle](/docs/plugin-lifecycle) — register / boot / enable / disable
- [Capabilities](/docs/capabilities) — the extension contracts
- [Authoring helpers](/docs/authoring-helpers) — reduce boilerplate in `boot()`
- [Plugin dependencies](/docs/plugin-dependencies) — require and conflict with other plugins
- [Testing](/docs/plugin-testing) — first-class test support

**Guides**
- [Best practices](/docs/best-practices) — patterns and anti-patterns
- [Security & performance](/docs/security-and-performance) — write safe, fast plugins
- [Troubleshooting & FAQ](/docs/troubleshooting) — diagnose common problems
- [Publishing & releasing](/docs/publishing) — versioning, CI, distribution, upgrades

**Reference**
- [Developer tooling](/docs/developer-tooling) — the Artisan commands
- [API stability](/docs/api-stability) — what will and won't break
- [Performance & scalability](/docs/performance) — how Magna stays fast at scale

Start with [Installation](/docs/installation).
