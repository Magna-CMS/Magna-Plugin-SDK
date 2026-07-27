# Security Policy

## Reporting a vulnerability

**Do not open a public issue for security vulnerabilities.**

The Plugin SDK is part of the Magna CMS project. Report privately via the Magna
CMS [GitHub Security Advisories](https://github.com/Magna-CMS/Magna/security/advisories/new)
("Report a vulnerability" on the Security tab). You will receive an
acknowledgement within 72 hours, and we follow coordinated disclosure.

## Scope

This package is the plugin **authoring API** (contracts, base class, manifest,
dependency resolver). It carries no runtime privileges of its own — a plugin's
security posture depends on the plugin's own code and on Magna Core, which
enforces the plugin trust model (permission checks, boot-time tamper reversion,
manifest validation).

## Supported versions

Pre-release. From 1.0 onward, the current and previous minor receive security
fixes.
