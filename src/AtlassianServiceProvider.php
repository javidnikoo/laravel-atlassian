<?php

namespace Javidnikoo\LaravelAtlassian;

use Illuminate\Support\ServiceProvider;

class AtlassianServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/atlassian.php' => config_path('atlassian.php'),
        ], 'atlassian-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/atlassian.php', 'atlassian');
    }
}
