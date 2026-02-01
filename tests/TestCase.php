<?php

namespace Javidnikoo\LaravelAtlassian\Tests;

use Illuminate\Support\Facades\Http;
use Javidnikoo\LaravelAtlassian\AtlassianServiceProvider;
use Javidnikoo\LaravelAtlassian\Jira\Facades\Jira;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [AtlassianServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Jira' => Jira::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('atlassian.confluence', [
            'base_url' => 'https://test.atlassian.net/wiki',
            'email' => 'test@example.com',
            'api_token' => 'abc123token',
            'timeout' => 15,
            'retries' => 2,
            'retry_delay_ms' => 500,
            'api_version' => 'v2',
        ]);

        $app['config']->set('atlassian.jira', [
            'base_url' => 'https://test.atlassian.net',
            'email' => 'test@example.com',
            'api_token' => 'abc123token',
            'timeout' => 15,
            'retries' => 2,
            'retry_delay_ms' => 500,
            'api_version' => '3',
        ]);
    }
}
