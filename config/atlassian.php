<?php

return [
    'confluence' => [
        'base_url' => env('ATLASSIAN_CONFLUENCE_BASE_URL'),
        'email' => env('ATLASSIAN_EMAIL'),
        'api_token' => env('ATLASSIAN_API_TOKEN'),
        'timeout' => env('ATLASSIAN_TIMEOUT', 30),
        'retries' => env('ATLASSIAN_RETRIES', 3),
        'retry_delay_ms' => env('ATLASSIAN_RETRY_DELAY_MS', 1000),
    ],
    'jira' => [
        'base_url' => env('ATLASSIAN_JIRA_BASE_URL'),
        'email' => env('ATLASSIAN_EMAIL'),
        'api_token' => env('ATLASSIAN_API_TOKEN'),
        'timeout' => env('ATLASSIAN_TIMEOUT', 30),
        'retries' => env('ATLASSIAN_RETRIES', 3),
        'retry_delay_ms' => env('ATLASSIAN_RETRY_DELAY_MS', 1000),
    ],
];
