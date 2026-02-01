<?php

namespace Javidnikoo\LaravelAtlassian\Tests;

use Illuminate\Support\Facades\Http;
use Javidnikoo\LaravelAtlassian\AtlassianServiceProvider;
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
}
