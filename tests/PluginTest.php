<?php

declare(strict_types=1);

namespace Magna\PluginSdk\Tests;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Magna\Plugins\Manifest;
use Magna\Plugins\Plugin;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    private function manifest(): Manifest
    {
        return Manifest::fromArray([
            'name' => 'acme/hello',
            'displayName' => 'Hello',
            'description' => 'An example plugin.',
            'version' => '1.0.0',
            'author' => 'Acme',
            'license' => 'MIT',
            'compat' => ['magna' => '^1.0', 'php' => '^8.3'],
            'entry' => 'Acme\Hello\HelloPlugin',
            'permissions' => [],
        ]);
    }

    /**
     * A Plugin subclass that exposes the protected authoring helpers so they
     * can be exercised directly.
     */
    private function plugin(Application $app, string $basePath = '/srv/plugins/hello'): Plugin
    {
        return new class($app, $basePath, $this->manifest()) extends Plugin
        {
            public function publicRoutesPath(string $file = 'api.php'): string
            {
                return $this->routesPath($file);
            }

            public function callLoadRoutesFrom(string $file, string $middleware = 'web', ?string $prefix = null): void
            {
                $this->loadRoutesFrom($file, $middleware, $prefix);
            }

            public function callLoadViewsFrom(string $path, string $namespace): void
            {
                $this->loadViewsFrom($path, $namespace);
            }

            public function callMergeConfigFrom(string $file, string $key): void
            {
                $this->mergeConfigFrom($file, $key);
            }

            /** @param class-string $event */
            public function callListen(string $event, string $listener): void
            {
                $this->listen($event, $listener);
            }
        };
    }

    public function test_it_exposes_manifest_and_base_path(): void
    {
        $plugin = $this->plugin($this->createMock(Application::class));

        $this->assertSame('acme/hello', $plugin->getManifest()->name);
        $this->assertSame('/srv/plugins/hello', $plugin->getBasePath());
    }

    public function test_lifecycle_hooks_default_to_no_ops(): void
    {
        $plugin = $this->plugin($this->createMock(Application::class));

        $plugin->register();
        $plugin->boot();
        $plugin->enable();
        $plugin->disable();

        $this->assertTrue(true);
    }

    public function test_routes_path_resolves_against_base_path(): void
    {
        $plugin = $this->plugin($this->createMock(Application::class));

        $this->assertSame('/srv/plugins/hello/routes/api.php', $plugin->publicRoutesPath());
        $this->assertSame('/srv/plugins/hello/routes/web.php', $plugin->publicRoutesPath('web.php'));
    }

    public function test_load_routes_from_groups_the_file_under_middleware_and_prefix(): void
    {
        $router = $this->createMock(Registrar::class);
        $router->expects($this->once())
            ->method('group')
            ->with(
                ['middleware' => 'api', 'prefix' => 'v1/blog'],
                '/srv/plugins/hello/routes/api.php',
            );

        $plugin = $this->plugin($this->appReturning('router', $router));
        $plugin->callLoadRoutesFrom('routes/api.php', 'api', 'v1/blog');
    }

    public function test_load_views_from_registers_the_namespace(): void
    {
        $view = $this->createMock(ViewFactory::class);
        $view->expects($this->once())
            ->method('addNamespace')
            ->with('blog', '/srv/plugins/hello/resources/views');

        $plugin = $this->plugin($this->appReturning('view', $view));
        $plugin->callLoadViewsFrom('resources/views', 'blog');
    }

    public function test_merge_config_from_lets_existing_values_win(): void
    {
        $dir = sys_get_temp_dir().'/magna_sdk_cfg_'.uniqid();
        mkdir($dir.'/config', 0777, true);
        file_put_contents($dir.'/config/blog.php', '<?php return ["per_page" => 10, "theme" => "default"];');

        $config = $this->createMock(Config::class);
        $config->method('get')->with('blog', [])->willReturn(['per_page' => 25]);
        $config->expects($this->once())
            ->method('set')
            ->with('blog', ['per_page' => 25, 'theme' => 'default']);

        try {
            $plugin = $this->plugin($this->appReturning('config', $config), $dir);
            $plugin->callMergeConfigFrom('config/blog.php', 'blog');
        } finally {
            unlink($dir.'/config/blog.php');
            rmdir($dir.'/config');
            rmdir($dir);
        }
    }

    public function test_listen_registers_on_the_dispatcher(): void
    {
        $events = $this->createMock(Dispatcher::class);
        $events->expects($this->once())
            ->method('listen')
            ->with('Acme\\Blog\\Events\\PostPublished', 'Acme\\Blog\\Listeners\\NotifySubscribers');

        $plugin = $this->plugin($this->appReturning('events', $events));
        $plugin->callListen('Acme\\Blog\\Events\\PostPublished', 'Acme\\Blog\\Listeners\\NotifySubscribers');
    }

    /**
     * Build an Application mock whose make($key) returns $service.
     */
    private function appReturning(string $key, object $service): Application
    {
        $app = $this->createMock(Application::class);
        $app->method('make')->willReturnCallback(
            fn (string $abstract): object => $abstract === $key
                ? $service
                : throw new \RuntimeException("Unexpected make({$abstract})"),
        );

        return $app;
    }
}
