<?php

namespace YorCreative\Scanator;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;
use YorCreative\Scanator\Configurations\ScanatorSqlConfiguration;
use YorCreative\Scanator\Services\ScanatorService;
use YorCreative\Scanator\Services\ScanatorServiceContract;
use YorCreative\Scrubber\ScrubberServiceProvider;
use YorCreative\Scrubber\Services\ScrubberService;

class ScanatorProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(ScanatorServiceContract::class, function (Application $app) {
            // @todo add more database configurations and pivot what configuration is built
            $config = (new ScanatorSqlConfiguration())
                ->setSqlIgnoreColumns(config('scanator.sql.ignore_columns'))
                ->setSqlIgnoreTables(config('scanator.sql.ignore_tables'))
                ->setSqlIgnoreTypes(config('scanator.sql.ignore_types'))
                ->setSqlLimitHigh(config('scanator.sql.select.high_limit'))
                ->setSqlLimitLow(config('scanator.sql.select.low_limit'));

            return new ScanatorService(
                $app->make(ConnectionInterface::class),
                ScrubberService::getRegexRepository(),
                $config
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__, 1).'/config/scanator.php', 'scanator');

        $this->app->register(ScrubberServiceProvider::class);

        $this->publishes([
            dirname(__DIR__, 1).'/config' => base_path('config'),
        ]);
    }

    public function provides()
    {
        return [
            ScanatorServiceContract::class,
        ];
    }
}
