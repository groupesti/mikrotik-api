<?php

declare(strict_types=1);

namespace MikroTik;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use MikroTik\Client\Builder\RouterOSBuilder;

final class MikroTikServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mikrotik.php', 'mikrotik');

        $this->app->singleton('mikrotik.routeros', function (Container $app): RouterOSBuilder {
            return new RouterOSBuilder();
        });
    }

    public function boot(): void
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                \MikroTik\Console\InspectRouterOSCommand::class,
                \\MikroTik\\Console\\MakeUiScaffoldCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/mikrotik.php' => config_path('mikrotik.php'),
        ], 'mikrotik-config');

        $this->publishes([
            __DIR__ . '/../resources/routeros/schema-7.21.3.json' => base_path('resources/routeros/schema-7.21.3.json'),
        ], 'mikrotik-schema');
    }
}
