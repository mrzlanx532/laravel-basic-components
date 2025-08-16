<?php

namespace Mrzlanx532\LaravelBasicComponents\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelBasicComponentsProvider extends ServiceProvider
{
    public function boot()
    {
        $currentDirectory = __DIR__;

        $this->publishes([
            "$currentDirectory/../../config/laravel_basic_components.php" => config_path('laravel_basic_components.php'),
        ], 'laravel-basic-components-config');

        $this->publishes([
            "$currentDirectory/../../database/migrations/create_Mrzlanx532_browser_filters_presets.php" => database_path('migrations/' . date('Y_m_d_His') . '_create_Mrzlanx532_browser_filters_presets.php'),
            "$currentDirectory/../../database/migrations/create_files.php" => database_path('migrations/' . date('Y_m_d_His') . '_create_files.php')
        ], 'laravel-basic-components-migrations');
    }
}