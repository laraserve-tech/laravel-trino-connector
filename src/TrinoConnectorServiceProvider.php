<?php

namespace LaraserveTech;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class TrinoConnectorServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/trino-connector.php',
            'trino-connector'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->offerPublishing();
        }
    }

    /**
     * @return $this
     */
    protected function offerPublishing(): self
    {
        $this->publishes([
            __DIR__ . '/../config/trino-connector.php' => config_path('trino-connector.php'),
        ], 'trino-connector-config');

        return $this;
    }
}
