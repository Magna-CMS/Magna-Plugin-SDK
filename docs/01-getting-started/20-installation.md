# Installation

There are two things you may want to install: **Magna itself** (the CMS), and the
**Plugin SDK** (to build a plugin). Most developers do both.

## Install Magna — the web installer (recommended)

The simplest way to get Magna running is the built-in web installer. No terminal
required:

1. Download the latest Magna release archive.
2. Extract it to the web root of your domain or subdomain (for example the
   folder your `example.com` or `docs.example.com` serves from).
3. Visit the domain in a browser. Magna detects that it is not yet installed and
   automatically launches the installer.
4. Follow the wizard — it checks server requirements, collects your database
   details, runs the migrations, and creates the first administrator account.

When the wizard finishes, your site is live and the admin panel is available at
the domain root (`/`).

### Requirements

- PHP 8.3 or newer
- A database (MySQL/MariaDB or PostgreSQL in production; SQLite for local dev)
- The standard Laravel PHP extensions (`mbstring`, `openssl`, `pdo`, `tokenizer`,
  `xml`, `ctype`, `json`, `fileinfo`)

### Command-line install (optional)

If you manage servers from the terminal, you can install with Composer and
Artisan instead of the web wizard — clone/require the project, set your `.env`,
then run `php artisan migrate` and create an admin user. The web installer simply
automates these same steps.

## Set up for plugin development

To build a plugin, add the SDK to your plugin package:

```bash
composer require magna-cms/plugin-sdk
```

Your plugin is an ordinary Composer package with `"type": "magna-plugin"`. During
development you can work on it in place under `plugins-dev/{vendor}/{package}/`
inside a Magna app, or in its own repository wired in as a Composer path
repository.

### Enabling a plugin

Magna discovers plugins automatically but never enables them without an explicit,
deliberate action (a plugin runs with full application access). Enable one from
the terminal:

```bash
php artisan magna:plugin:enable acme/blog
```

or from the admin panel's **Plugins** screen. Enabling runs the plugin's
migrations, registers its permissions and routes, and boots it.

Next: [create your first plugin](/docs/creating-a-plugin).
