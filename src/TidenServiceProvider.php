<?php

declare(strict_types=1);

namespace Tiden\Laravel;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Tiden\Sdk;
use Throwable;

/**
 * Auto-discovered Laravel integration. Initializes the Tiden SDK from config and
 * registers a reportable callback so every exception Laravel reports is also sent
 * to Tiden — no changes to bootstrap/app.php required.
 */
final class TidenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tiden.php', 'tiden');
    }

    public function boot(): void
    {
        /** @var array<string,mixed> $config */
        $config = (array) $this->app['config']->get('tiden', []);

        if (!empty($config['dsn'])) {
            // Laravel owns global error handling, so the core SDK's own handlers
            // are disabled — capture flows through the reportable callback below.
            Sdk::init([
                'dsn' => $config['dsn'],
                'release' => $config['release'] ?? null,
                'environment' => $config['environment'] ?? null,
                'send_default_pii' => (bool) ($config['send_default_pii'] ?? false),
            ], captureGlobals: false);

            $handler = $this->app->make(ExceptionHandler::class);
            if (method_exists($handler, 'reportable')) {
                $handler->reportable(static function (Throwable $e): void {
                    Sdk::captureException($e);
                });
            }
        }

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__ . '/../config/tiden.php' => $this->app->configPath('tiden.php')],
                'tiden-config',
            );
        }
    }
}
