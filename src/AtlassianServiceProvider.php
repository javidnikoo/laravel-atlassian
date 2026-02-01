<?php

namespace Javidnikoo\LaravelAtlassian;

use Illuminate\Support\ServiceProvider;
use Javidnikoo\LaravelAtlassian\Jira\Clients\JiraClient;
use Javidnikoo\LaravelAtlassian\Jira\Contracts\JiraClientInterface;

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

        $this->app->singleton(JiraClientInterface::class, fn ($app) => new JiraClient(
            $app['config']->get('atlassian.jira', [])
        ));
    }
}
