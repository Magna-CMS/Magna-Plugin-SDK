<?php

declare(strict_types=1);

namespace Magna\Plugins;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\View\Factory as ViewFactory;

/**
 * Base class every Magna plugin entry class must extend.
 *
 * Lifecycle (managed by PluginManager):
 *   - register() — bind services; called once per request bootstrap, before boot()
 *   - boot()     — load routes, listeners, etc.; called after all plugins have registered
 *   - enable()   — one-time: called when an admin enables the plugin
 *   - disable()  — one-time: called when an admin disables the plugin
 *
 * The protected loadRoutesFrom / loadViewsFrom / mergeConfigFrom / listen
 * helpers are thin, explicitly-named
 * wrappers over Laravel's own services (resolved through the injected container)
 * so a plugin's boot() reads as intent, not container plumbing. All paths are
 * relative to the plugin's own directory. They are deliberately dependency-free
 * (every service is an illuminate/contracts interface) to honour the SDK's
 * one-way boundary; translation/command registration, which Laravel exposes
 * only on concrete classes, is left to plain `$this->app` calls.
 */
abstract class Plugin
{
    public function __construct(
        protected readonly Application $app,
        protected readonly string $basePath,
        protected readonly Manifest $manifest,
    ) {}

    public function register(): void {}

    public function boot(): void {}

    public function enable(): void {}

    public function disable(): void {}

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Absolute path to one of the plugin's route files, for use inside boot():
     * `$router->group($this->routesPath('web.php'))`. Migrations are discovered
     * and run automatically from `database/migrations/` — a plugin never needs
     * to reference that path itself.
     */
    protected function routesPath(string $file = 'api.php'): string
    {
        return $this->basePath.'/routes/'.$file;
    }

    /**
     * Register a route file under the given middleware group (and optional URL
     * prefix). Replaces the boilerplate
     * `$this->app['router']->middleware('web')->group(fn () => require ...)`.
     *
     * @param  string  $file  Path relative to the plugin root, e.g. "routes/web.php".
     */
    protected function loadRoutesFrom(string $file, string $middleware = 'web', ?string $prefix = null): void
    {
        /** @var Registrar $router */
        $router = $this->app->make('router');

        $attributes = ['middleware' => $middleware];
        if ($prefix !== null) {
            $attributes['prefix'] = $prefix;
        }

        $router->group($attributes, $this->basePath.'/'.$file);
    }

    /**
     * Register a view namespace so the plugin's Blade files resolve as
     * `view('namespace::template')`.
     *
     * @param  string  $path  Path relative to the plugin root, e.g. "resources/views".
     */
    protected function loadViewsFrom(string $path, string $namespace): void
    {
        /** @var ViewFactory $view */
        $view = $this->app->make('view');
        $view->addNamespace($namespace, $this->basePath.'/'.$path);
    }

    /**
     * Merge a plugin config file under the given key, with any values already
     * present (e.g. published overrides) taking precedence — same semantics as
     * a Laravel service provider's mergeConfigFrom().
     *
     * @param  string  $file  Path relative to the plugin root, e.g. "config/blog.php".
     */
    protected function mergeConfigFrom(string $file, string $key): void
    {
        /** @var Config $config */
        $config = $this->app->make('config');

        /** @var array<string, mixed> $defaults */
        $defaults = require $this->basePath.'/'.$file;

        $existing = $config->get($key, []);
        $config->set($key, array_merge($defaults, is_array($existing) ? $existing : []));
    }

    /**
     * Register an event listener. Thin wrapper over the event dispatcher so a
     * plugin's boot() does not need to resolve it by hand.
     *
     * @param  class-string  $event
     * @param  class-string|\Closure  $listener  A listener class name or a closure.
     */
    protected function listen(string $event, string|\Closure $listener): void
    {
        /** @var Dispatcher $events */
        $events = $this->app->make('events');
        $events->listen($event, $listener);
    }
}
