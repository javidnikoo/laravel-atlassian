<?php

namespace Javidnikoo\LaravelAtlassian\Jira\Contracts;

use Javidnikoo\LaravelAtlassian\Jira\Features\Issue\Resource\IssueResource;

interface JiraClientInterface
{
    public function issues(): IssueResource;
}
