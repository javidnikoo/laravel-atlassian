<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Facades;

use Illuminate\Support\Facades\Facade;
use Javidnikoo\LaravelAtlassian\Jira\Contracts\JiraClientInterface;

class Jira extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return JiraClientInterface::class;
    }
}
