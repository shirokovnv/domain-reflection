<?php

namespace Shirokovnv\DomainReflection;

use Illuminate\Support\ServiceProvider;
use Shirokovnv\DomainReflection\Commands\InitDomain;
use Shirokovnv\DomainReflection\Commands\ReloadModel;
use Shirokovnv\DomainReflection\Commands\RemoveModel;
use Shirokovnv\ModelReflection\ModelReflection;
use Illuminate\Support\Facades\DB;

/**
 * Class DomainReflectionServiceProvider
 * @package Shirokovnv\DomainReflection
 */
class DomainReflectionServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/domain-reflection.php', 'domain-reflection');

        // Register the service the package provides.
        $this->app->singleton('domain-reflection', function ($app) {
            return new DomainReflection($app->makeWith(ModelReflection::class, ['conn' => DB::connection()]));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['domain-reflection'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/domain-reflection.php' => config_path('domain-reflection.php'),
        ], 'config');

        // Registering package commands.
        $this->commands([InitDomain::class, ReloadModel::class, RemoveModel::class]);
    }
}
